<?php

namespace App\Http\Controllers\GM;

use Illuminate\Http\Request;
use App\Models\Manager\Role;
use App\Models\Manager\Admin;
use App\Models\ConfigAttribute;
use App\Models\Manager\ActionLog;
use App\Models\Node;
use App\Models\NodeRoom;
use App\Models\ConfigGame;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Http\Library\DynamicJsonForm;
use App\Http\Library\Server;
use App\Models\NodeEntrance;
use App\Models\RoomLists;
use App\Rules\XML;
use Illuminate\Support\Facades\Log;
class RoomController extends GMController
{
    public function roomView(Request $request)
    {
        return view('GM/Room/roomView', [
            'pageTitle' => $this->role->getCurrentPageTitle($request),
            'role' => new Role, 'request' => $request
        ]);
    }

    public function roomList(Request $request)
    {
        // Log::info('aaaa', [1, 2, 3], 11,  333, [1]);
        $limit = $request->query->get('limit', 20);
        $offset = $request->query->get('offset', 0);
        $sort = $request->query->get('sort', 'id');
        $order = $request->query->get('order', 'desc');
        $roomID = $request->query->get('room_id');
        // $gameID = $request->query->get('gameid');


        $model = new RoomLists();
        !empty($sort) && $model = $model->orderBy($sort, $order);
        $roomID && $model = $model->where('room_lists.room_id', $roomID);
        // $gameID && $model = $model->where('gameid', $gameID);
        // $model = $model->select('gm_admin.id', 'username', 'gm_role.name', 'is_lock', 'last_login_ip', 'last_update_password_time', 'gm_admin.created');
        $model = $model->leftjoin('gm_admin', 'gm_admin.id', '=', 'room_lists.c_adminid');
        $model = $model->leftjoin('gm_admin as gm_admin_last', 'gm_admin_last.id', '=', 'room_lists.last_m_adminid');

        $model = $model->select(
            'room_lists.id',
            'room_lists.room_id',
            'room_lists.channel_id',
            'room_lists.status',
            'room_lists.desc',
            // 'room_lists.c_adminid',
            $model->raw('from_unixtime(room_lists.c_time) as c_time'),
            $model->raw('from_unixtime(room_lists.m_time) as m_time'),
            // $model->raw('DATE_FORMAT(room_lists.c_time, "%m-%d-%Y %h:%i:%s") as c_time'),
            // $model->raw('DATE_FORMAT(room_lists.m_time, "%m-%d-%Y %h:%i:%s") as m_time'),
            // 'room_lists.last_m_adminid',
            // 'room_lists.m_time',
            'room_lists.uid_tails',
            'room_lists.max_num',

            'gm_admin.username as c_adminid',
            'gm_admin_last.username as last_m_adminid'
        );
        $total = $model->count();
        $rows = $model->offset($offset)->limit($limit)->get()->toArray();
        return [
            'result' => [],
            'rows' => $rows,
            'success' => 1,
            'total' => $total,
        ];
    }

    public function roomDetail(Request $request, $id)
    {
        $data = RoomLists::select(
            'id',
            'room_id',
            'channel_id',
            'status',
            'desc',
            'c_adminid',
            'c_time',
            'last_m_adminid',
            'm_time',
            'uid_tails',
            'max_num'
        )
            ->where('id', $id)->first();
        return ['success' => 1, 'data' => $data];
    }

    public function roomAdd(Request $request)
    {

        $message = [
            'channel_id.unique' => 'Given room_id and channel_id are not unique',
        ];

        $roomID = $request->get('room_id');
        $channelID = $request->get('channel_id');

        $validator = Validator::make($request->all(), [
            'room_id' => ['required', 'integer'],
            'channel_id' => ['required', 'integer', Rule::unique('Master.room_lists')->where(function ($query) use ($roomID, $channelID) {
                return $query->where('room_id', $roomID)->where('channel_id', $channelID);
            })],
            'uid_tails' => ['string', 'max:30'],
            'max_num' => ['required', 'integer'],
            'status' => ['required', 'integer', Rule::in(['0', '1'])],
            'desc' => ['required', 'string', 'max:255'],
        ], $message);

        if ($validator->fails()) {
            return ['success' => 0, 'result' => $validator->errors()->first(), 'validator' => $validator->errors()];
        }
        // echo 1;exit;
        $data = $request->only(
            'room_id',
            'channel_id',
            'desc',
            'uid_tails',
            'max_num',
            'status'
        );

        $data['c_time'] = time();
        $data['c_adminid'] = $this->admin->getLoginID($request);

        $data['m_time'] = time();
        $data['last_m_adminid'] = $this->admin->getLoginID($request);

        $after = RoomLists::create($data);
        ActionLog::create([
            'admin_id' => $this->admin->getLoginID($request),
            'admin_username' => $this->admin->getLoginUsername($request),
            'browser' => $request->header('User-Agent'),
            'key' => 'ROOM_CREATE',
            'is_success' => 1,
            'url' => $request->url(),
            'ip' => $this->ip($request),
            'desc' => $after->desc,
            'target_id' => $after->id,
            'after' =>  $after->toJson(),
            'params' => json_encode($request->all()),
            'method' => $request->method()
        ]);

        $server = new Server;
        $server->updateRoomNotify([
            'room_id' => $data['room_id'],
            'channel_id' => $data['channel_id'],
            'status' => $data['status'],
            'uid_tails' => $data['uid_tails'] ?? '',
            'max_num' => $data['max_num'],
        ]);
        return ['success' => 1, 'result' => __('ts.create success')];
    }

    public function roomEdit(Request $request, $id)
    {
        $before = RoomLists::select(
            'id',
            'room_id',
            'channel_id',
            'status',
            'desc',
            'c_adminid',
            'c_time',
            'last_m_adminid',
            'm_time',
            'uid_tails',
            'max_num'
        )->where('id', $id)->first();


        $validator = Validator::make($request->all(), [
            'uid_tails' => ['string', 'max:30'],
            'max_num' => ['required', 'integer'],
            'desc' => ['required', 'string', 'max:255'],
            'status' => ['required', 'integer', Rule::in(['0', '1'])],
        ]);

        if ($validator->fails()) {
            return ['success' => 0, 'result' => $validator->errors()->first(), 'validator' => $validator->errors()];
        }

        $data = $request->only(
            'uid_tails',
            'max_num',
            'desc',
            'status'
        );
        $data['m_time'] = time();
        $data['last_m_adminid'] = $this->admin->getLoginID($request);

        RoomLists::where('id', $id)->update($data);
        $after = RoomLists::select(
            'id',
            'room_id',
            'channel_id',
            'status',
            'desc',
            'c_adminid',
            'c_time',
            'last_m_adminid',
            'm_time',
            'uid_tails',
            'max_num'
        )->where('id', $id)->first();
        $collection = collect($before);
        $diffItems = $collection->diff($after);
        if (!$diffItems->isEmpty()) {
            ActionLog::create([
                'admin_id' => $this->admin->getLoginID($request),
                'admin_username' => $this->admin->getLoginUsername($request),
                'browser' => $request->header('User-Agent'),
                'key' => 'ROOM_EDIT',
                'is_success' => 1,
                'url' => $request->url(),
                'ip' => $this->ip($request),
                'desc' => $before->desc,
                'target_id' => $before->id,
                'before' => $before->toJson(),
                'after' =>  $after->toJson(),
                'params' => json_encode($request->all()),
                'method' => $request->method()
            ]);
        }

        $server = new Server;
        $server->updateRoomNotify([
            'room_id' => $after->room_id,
            'channel_id' => $after->channel_id,
            'status' => $after->status,
            'uid_tails' => $after->uid_tails,
            'max_num' => $after->max_num,
        ]);
        return ['success' => 1, 'result' => __('ts.update success')];
    }

    public function roomEnabledEdit(Request $request, $id)
    {
        $before = RoomLists::select(
            'id',
            'room_id',
            'channel_id',
            'status',
            'desc',
            'c_adminid',
            'c_time',
            'last_m_adminid',
            'm_time',
            'uid_tails',
            'max_num'
        )->where('id', $id)->first();

        $validator = Validator::make($request->all(), [
            'status' => ['required', Rule::in(['0', '1'])],
        ]);

        if ($validator->fails()) {
            return ['success' => 0, 'result' => $validator->errors()->first(), 'validator' => $validator->errors()];
        }

        $data = $request->only(
            'status'
        );

        RoomLists::where('id', $id)->update($data);
        $after = RoomLists::select(
            'id',
            'room_id',
            'channel_id',
            'status',
            'desc',
            'c_adminid',
            'c_time',
            'last_m_adminid',
            'm_time',
            'uid_tails',
            'max_num'
        )->where('id', $id)->first();
        $collection = collect($before);
        $diffItems = $collection->diff($after);
        if (!$diffItems->isEmpty()) {
            ActionLog::create([
                'admin_id' => $this->admin->getLoginID($request),
                'admin_username' => $this->admin->getLoginUsername($request),
                'browser' => $request->header('User-Agent'),
                'key' => 'ROOM_ENABLED_EDIT',
                'is_success' => 1,
                'url' => $request->url(),
                'ip' => $this->ip($request),
                'desc' => $before->username,
                'target_id' => $before->id,
                'before' => $before->toJson(),
                'after' =>  $after->toJson(),
                'params' => json_encode($request->all()),
                'method' => $request->method()
            ]);
        }

        $server = new Server;
        $server->updateRoomNotify([
            'room_id' => $after->room_id,
            'channel_id' => $after->channel_id,
            'status' => $after->status,
            // 'uid_tails' => $after->uid_tails,
            // 'max_num' => $after->max_num,
        ]);
        return ['success' => 1, 'result' => __('ts.update success')];
    }

    public function roomDel(Request $request, $id)
    {
        $before = RoomLists::select(
            'id',
            'room_id',
            'channel_id',
            'status',
            'desc',
            'c_adminid',
            'c_time',
            'last_m_adminid',
            'm_time',
            'uid_tails',
            'max_num'
        )->where('id', $id)->first();
        if (empty($before)) {
            return ['success' => 0, 'result' => __('ts.id error')];
        }

        if ($before->status == 0) {
            return ['success' => 0, 'result' => __('ts.The room is being activated and cannot be deleted')];
        }
        RoomLists::where('id', $id)->delete();
        ActionLog::create([
            'admin_id' => $this->admin->getLoginID($request),
            'admin_username' => $this->admin->getLoginUsername($request),
            'browser' => $request->header('User-Agent'),
            'key' => 'ROOM_DELETE',
            'is_success' => 1,
            'url' => $request->url(),
            'ip' => $this->ip($request),
            'desc' => $before->desc,
            'target_id' => $before->id,
            'before' => $before->toJson(),
            'params' => json_encode($request->all()),
            'method' => $request->method()
        ]);
        return ['success' => 1, 'result' => __('ts.delete success')];
    }
}
