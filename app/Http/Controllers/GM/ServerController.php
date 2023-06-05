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
use App\Rules\XML;

class ServerController extends GMController
{
    public function roomView(Request $request)
    {
        return view('GM/Server/roomView', [
            'pageTitle' => $this->role->getCurrentPageTitle($request),
            'role' => new Role, 'request' => $request
        ]);
    }

    public function roomList(Request $request)
    {
        $limit = $request->query->get('limit', 20);
        $offset = $request->query->get('offset', 0);
        $sort = $request->query->get('sort', 'id');
        $order = $request->query->get('order', 'desc');
        $name = $request->query->get('name');
        $gameID = $request->query->get('gameid');


        $model = new Node();
        !empty($sort) && $model = $model->orderBy($sort, $order);
        $name && $model = $model->where('name', $name);
        $gameID && $model = $model->where('gameid', $gameID);
        // $model = $model->select('gm_admin.id', 'username', 'gm_role.name', 'is_lock', 'last_login_ip', 'last_update_password_time', 'gm_admin.created');
        // $model = $model->leftjoin('gm_role', 'gm_role.id', '=', 'gm_admin.role_id');

        $model = $model->select(
            'id',
            'parentid',
            // 'alias_id',
            'gameid',
            'name',
            'name_mark',
            'pic_name',
            'plat',
            'plats',
            'sortid',
            'mingold',
            'maxgold',
            'optime',
            'xmlgame',
            // 'roomchargegold',
            'enabled',
            'hot_register',
            'bottom',
            // 'play_method',
            'tiyan',
            // 'tax',
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

    public function roomPushConfig(Request $request)
    {
        $server = new Server();
        $return = $server->refreshRoom();

        ActionLog::create([
            'admin_id' => $this->admin->getLoginID($request),
            'admin_username' => $this->admin->getLoginUsername($request),
            'browser' => $request->header('User-Agent'),
            'key' => 'SERVER_ROOM_PUSH_CONFIG',
            'is_success' => $return['success'],
            'url' => $request->url(),
            'ip' => $this->ip($request),
            'desc' => '',
            'target_id' => 0,
            'after' =>  json_encode($return),
            'params' => json_encode($request->all()),
            'method' => $request->method()
        ]);
        return $return;
    }

    public function roomDetail(Request $request, $id)
    {
        $data = Node::select(
            'id',
            'gameid',
            'enabled',
            'name',
            // 'roomchargegold',
            'bottom',
            'mingold',
            'maxgold',
            'sortid',
            'hot_register',
            'pic_name',
            'xmlgame',
            'tiyan',
            // 'tax',
        )
            ->where('id', $id)->first();
        return ['success' => 1, 'data' => $data];
    }

    public function roomAdd(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'gameid' => ['required', 'integer'],
            'enabled' => ['required', Rule::in(['0', '1'])],
            'name' => ['required', 'string', 'max:20'],
            // 'roomchargegold' => ['required', 'integer'],
            'bottom' => ['required', 'integer'],
            'mingold' => ['required', 'integer'],
            'maxgold' => ['required', 'integer'],
            // 'tax' => ['required', 'integer'],
            // 'sortid' => ['required', 'integer', 'digits_between:1,999', 'unique:\App\Models\Node,sortid'],
            'sortid' => ['required', 'integer', 'digits_between:1,999'],
            'hot_register' => ['required', 'integer'],
            'pic_name' => ['required', 'string', 'max:256'],
            'xmlgame' => ['json'],
            'tiyan' => ['required', Rule::in(['0', '1'])],
        ]);

        if ($validator->fails()) {
            return ['success' => 0, 'result' => $validator->errors()->first(), 'validator' => $validator->errors()];
        }

        $data = $request->only(
            'gameid',
            'enabled',
            'name',
            // 'roomchargegold',
            'bottom',
            'mingold',
            'maxgold',
            'sortid',
            'hot_register',
            'pic_name',
            'xmlgame',
            'tiyan',
            // 'tax',
        );

        $data['optime'] = date("Y-m-d H:i:s", time());
        $after = Node::create($data);
        ActionLog::create([
            'admin_id' => $this->admin->getLoginID($request),
            'admin_username' => $this->admin->getLoginUsername($request),
            'browser' => $request->header('User-Agent'),
            'key' => 'MANAGER_ROOM_CREATE',
            'is_success' => 1,
            'url' => $request->url(),
            'ip' => $this->ip($request),
            'desc' => $after->name,
            'target_id' => $after->id,
            'after' =>  $after->toJson(),
            'params' => json_encode($request->all()),
            'method' => $request->method()
        ]);

        $server = new Server();
        $server->roomNotify($after->id);
        return ['success' => 1, 'result' => __('ts.create success')];
    }

    public function roomEdit(Request $request, $id)
    {
        $before = Node::select(
            'id',
            'gameid',
            // 'enabled',
            'name',
            // 'roomchargegold',
            'bottom',
            'mingold',
            'maxgold',
            'sortid',
            'hot_register',
            'pic_name',
            // 'xmlgame',
            'tiyan',
            // 'tax',
        )->where('id', $id)->first();

        // $sr = ['required', 'integer', 'digits_between:1,999', 'unique:\App\Models\Node,sortid'];
        // if ($before->sortid == $request->input('sortid')) {
            $sr = ['required', 'integer', 'digits_between:1,999'];
        // }

        $validator = Validator::make($request->all(), [
            'gameid' => ['required', 'integer'],
            // 'enabled' => ['required', Rule::in(['0', '1'])],
            'name' => ['required', 'string', 'max:20'],
            // 'roomchargegold' => ['required', 'integer'],
            'bottom' => ['required', 'integer'],
            'mingold' => ['required', 'integer'],
            'maxgold' => ['required', 'integer'],
            // 'tax' => ['required', 'integer'],
            'sortid' => $sr,
            'hot_register' => ['required', 'integer'],
            'pic_name' => ['required', 'string', 'max:256'],
            // 'xmlgame' => ['json'],
            'tiyan' => ['required', Rule::in(['0', '1'])],
        ]);

        if ($validator->fails()) {
            return ['success' => 0, 'result' => $validator->errors()->first(), 'validator' => $validator->errors()];
        }

        $data = $request->only(
            'gameid',
            // 'enabled',
            'name',
            // 'roomchargegold',
            'bottom',
            'mingold',
            'maxgold',
            'sortid',
            'hot_register',
            'pic_name',
            // 'xmlgame',
            'tiyan',
            // 'tax',
        );

        Node::where('id', $id)->update($data);
        $after = Node::select(
            'id',
            'gameid',
            // 'enabled',
            'name',
            // 'roomchargegold',
            'bottom',
            'mingold',
            'maxgold',
            'sortid',
            'hot_register',
            'pic_name',
            // 'xmlgame',
            'tiyan',
            // 'tax',
        )->where('id', $id)->first();
        $collection = collect($before);
        $diffItems = $collection->diff($after);
        if (!$diffItems->isEmpty()) {
            ActionLog::create([
                'admin_id' => $this->admin->getLoginID($request),
                'admin_username' => $this->admin->getLoginUsername($request),
                'browser' => $request->header('User-Agent'),
                'key' => 'SERVER_ROOM_EDIT',
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

        $server = new Server();
        $server->roomNotify($id);
        return ['success' => 1, 'result' => __('ts.update success')];
    }

    public function roomEnabledEdit(Request $request, $id)
    {
        $before = Node::select(
            'id',
            'enabled',
        )->where('id', $id)->first();

        $validator = Validator::make($request->all(), [
            'enabled' => ['required', Rule::in(['0', '1'])],
        ]);

        if ($validator->fails()) {
            return ['success' => 0, 'result' => $validator->errors()->first(), 'validator' => $validator->errors()];
        }

        $data = $request->only(
            'enabled',
        );

        Node::where('id', $id)->update($data);
        $after = Node::select(
            'id',
            'enabled',
        )->where('id', $id)->first();
        $collection = collect($before);
        $diffItems = $collection->diff($after);
        if (!$diffItems->isEmpty()) {
            ActionLog::create([
                'admin_id' => $this->admin->getLoginID($request),
                'admin_username' => $this->admin->getLoginUsername($request),
                'browser' => $request->header('User-Agent'),
                'key' => 'SERVER_ROOM_ENABLED_EDIT',
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
        return ['success' => 1, 'result' => __('ts.update success')];
    }

    public function roomDel(Request $request, $id)
    {
        $before = Node::select(
            'id',
            'parentid',
            // 'alias_id',
            'gameid',
            'name',
            'name_mark',
            'pic_name',
            'plat',
            'plats',
            'sortid',
            'mingold',
            'maxgold',
            'optime',
            'xmlgame',
            // 'roomchargegold',
            'enabled',
            'hot_register',
            'bottom',
            // 'play_method',
            'tiyan',
            // 'tax',
        )->where('id', $id)->first();
        if (empty($before)) {
            return ['success' => 0, 'result' => __('ts.id error')];
        }
        Node::where('id', $id)->delete();
        ActionLog::create([
            'admin_id' => $this->admin->getLoginID($request),
            'admin_username' => $this->admin->getLoginUsername($request),
            'browser' => $request->header('User-Agent'),
            'key' => 'SERVER_ROOM_DELETE',
            'is_success' => 1,
            'url' => $request->url(),
            'ip' => $this->ip($request),
            'desc' => $before->name,
            'target_id' => $before->id,
            'before' => $before->toJson(),
            'params' => json_encode($request->all()),
            'method' => $request->method()
        ]);

        $server = new Server();
        $server->roomNotify($id, 'delete');
        return ['success' => 1, 'result' => __('ts.delete success')];
    }

    public function roomProcessDetail(Request $request, $id)
    {
        $model = new NodeRoom();
        $data = $model->where('nodeid', $id)->get();
        return $this->success($data);
    }

    public function roomProcessEdit(Request $request, $id)
    {
        $model = new NodeRoom();

        $validator = Validator::make($request->all(), [
            'roomIDs' => ['required', new \App\Rules\IDsStr()],
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors()->first());
        }

        $ids = explode(',', $request->input('roomIDs'));
        $model->where('nodeid', $id)->delete();

        $before = $model->select(
            'nodeid',
            'playid',
            'roomid'
        )->get();

        foreach ($ids as $ir) {
            NodeRoom::create([
                'playid' => 0,
                'roomid' => $ir,
                'nodeid' => $id,
            ]);
        }

        $after = $model->select(
            'nodeid',
            'playid',
            'roomid'
        )->get();

        $collection = collect($before);
        $diffItems = $collection->diff($after);
        if (!$diffItems->isEmpty()) {
            ActionLog::create([
                'admin_id' => $this->admin->getLoginID($request),
                'admin_username' => $this->admin->getLoginUsername($request),
                'browser' => $request->header('User-Agent'),
                'key' => 'SERVER_ROOM_PROCESS_EDIT',
                'is_success' => 1,
                'url' => $request->url(),
                'ip' => $this->ip($request),
                'desc' => $id,
                'target_id' => $id,
                'before' => $before->toJson(),
                'after' => $after->toJson(),
                'params' => json_encode($request->all()),
                'method' => $request->method()
            ]);
        }

        $server = new Server();
        $server->roomNotify($id);
        return $this->success(__('ts.process update sucess'));
    }

    public function roomInventoryDetail(Request $request, $id)
    {
        $model = new Node();
        $nodeData = $model->where('id', $id)->first();
        $server = new Server();
        $data = $server->getInventory($nodeData['gameid'], $id);
        return $data;
    }

    public function roomInventoryEdit(Request $request, $id)
    {
        $model = new Node();
        $server = new Server();
        $nodeData = $model->where('id', $id)->first();

        $validator = Validator::make($request->all(), [
            'tax' => ['required', 'integer'],
            'actual_tax' => ['required', 'integer'],
            'pool_extract' => ['required', 'integer'],
            'actual_pool' => ['required', 'integer'],
            'pool_rate' => ['required', 'integer'],
            'min' => ['required', 'integer'],
            'actual_num' => ['required', 'integer'],
            'actual_stock' => ['required', 'integer'],
            'stock_min' => ['required', 'integer'],
            'stock_max' => ['required', 'integer'],
            'stock_switch' => ['required', 'integer'],
            'pool_max_lose' => ['required', 'integer'],
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors()->first(), ['validator' => $validator->errors()]);
        }

        $data = $request->only(
            'tax',
            'actual_tax',
            'pool_extract',
            'actual_pool',
            'pool_rate',
            'min',
            'actual_num',
            'actual_stock',
            'stock_min',
            'stock_max',
            'stock_switch',
            'pool_max_lose'
        );

        $ratio = $data['actual_pool'] / $data['min'];
        if ($ratio < 0.5 || $ratio > 1.5) {
            return $this->error('The ratio of the real-time prize pool and the minimum trigger value must be 50% -150%');
        }

        $before = $server->getInventory($nodeData['gameid'], $id);

        $data['num'] = 0;
        $data['gameid'] = $nodeData['gameid'];
        $data['nodeid'] = $nodeData['nodeid'];
        $data['name'] = $nodeData['name'];
        $data['enabled'] = $nodeData['enabled'];
        $res = $server->updateInventory($data);
        $after = $server->getInventory($nodeData['gameid'], $id);

        $collection = collect($before);
        $diffItems = $collection->diff($after);
        if (!$diffItems->isEmpty()) {
            ActionLog::create([
                'admin_id' => $this->admin->getLoginID($request),
                'admin_username' => $this->admin->getLoginUsername($request),
                'browser' => $request->header('User-Agent'),
                'key' => 'SERVER_ROOM_INVENTORY_EDIT',
                'is_success' => $res['success'],
                'url' => $request->url(),
                'ip' => $this->ip($request),
                'desc' => $id,
                'target_id' => $id,
                'before' => json_encode($before),
                'after' => json_encode($after),
                'params' => json_encode($request->all()),
                'method' => $request->method()
            ]);
        }

        return $res;
    }

    public function roomJSONEdit(Request $request, $id)
    {
        $model = new Node();
        $nodeData = $model->where('id', $id)->first();
        $configData = config('gm.json_form_config.' . $nodeData['gameid']);

        $actionButton = $request->input('actionButton');
        $actionField = $request->input('actionField');
        $json = $request->input('json', []);
        $json = !empty($json) ? $json : json_decode($nodeData['xmlgame'], true);

        if (empty($configData)) {
            return 'this game not support json config module:' . $nodeData['gameid'];
        }

        if ($actionButton) {
            $forms = (new DynamicJsonForm($configData))->$actionButton($actionField)->fill($json, false)->create();
        } else {
            $forms = (new DynamicJsonForm($configData))->fill($json, false);
            if ($request->method() == 'PATCH') {
                if ($forms->isValid()) {
                    $model->where('id', $id)->update([
                        'xmlgame' => json_encode($json)
                    ]);

                    // $collection = collect(json_decode($nodeData['xmlgame'], true));
                    // dd(json_decode($nodeData['xmlgame'], true));
                    // dd(json_decode($nodeData['xmlgame'], true), $json);
                    // $diffItems = $collection->diff($json);

                    if ($nodeData['xmlgame'] != json_encode($json)) {
                        ActionLog::create([
                            'admin_id' => $this->admin->getLoginID($request),
                            'admin_username' => $this->admin->getLoginUsername($request),
                            'browser' => $request->header('User-Agent'),
                            'key' => 'SERVER_ROOM_JSON_EDIT',
                            'is_success' => 1,
                            'url' => $request->url(),
                            'ip' => $this->ip($request),
                            'desc' => $id,
                            'target_id' => $id,
                            'before' => $nodeData['xmlgame'],
                            'after' => json_encode($json),
                            'params' => json_encode($request->all()),
                            'method' => $request->method()
                        ]);
                    }

                    $server = new Server();
                    $server->roomNotify($id);
                    return 'success';
                }
            }
            $forms = $forms->create();
        }
        return view('GM/Server/roomJSONView', ['forms' => $forms]);
    }

    public function playView(Request $request)
    {
        return view('GM/Server/playView', ['pageTitle' => $this->role->getCurrentPageTitle($request)]);
    }

    public function playList(Request $request)
    {
        $limit = $request->query->get('limit', 20);
        $offset = $request->query->get('offset', 0);
        $sort = $request->query->get('sort', 'id');
        $order = $request->query->get('order', 'desc');
        $methodName = $request->query->get('method_name');

        $model = new NodeEntrance();
        !empty($sort) && $model = $model->orderBy($sort, $order);
        $methodName && $model = $model->where('method_name', $methodName);

        $model = $model->select(
            'id',
            'game_id',
            'game_type',
            'method_name',
            'pict_url'
        );
        $total = $model->count();
        $rows = $model->offset($offset)->limit($limit)->get()->toArray();
        return $this->success([], ['rows' => $rows, 'total' => $total]);
    }

    public function playDetail(Request $request, $id)
    {
        $data = NodeEntrance::select(
            'id',
            'game_id',
            'game_type',
            'method_name',
            'pict_url',
        )
            ->where('id', $id)->first();
        return $this->success([], ['data' => $data]);
    }

    public function PlayDel(Request $request, $id)
    {
        $before = NodeEntrance::select(
            'id',
            'game_id',
            'game_type',
            'method_name',
            'pict_url'
        )->where('id', $id)->first();
        if (empty($before)) {
            return ['success' => 0, 'result' => __('ts.id error')];
        }
        NodeEntrance::where('id', $id)->delete();
        ActionLog::create([
            'admin_id' => $this->admin->getLoginID($request),
            'admin_username' => $this->admin->getLoginUsername($request),
            'browser' => $request->header('User-Agent'),
            'key' => 'SERVER_PLAY_DELETE',
            'is_success' => 1,
            'url' => $request->url(),
            'ip' => $this->ip($request),
            'desc' => $before->name,
            'target_id' => $before->id,
            'before' => $before->toJson(),
            'params' => json_encode($request->all()),
            'method' => $request->method()
        ]);
        return ['success' => 1, 'result' => __('ts.delete success')];
    }

    public function PlayEdit(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'game_id' => ['required', 'integer'],
            'game_type' => ['required', 'string', 'max:50'],
            'method_name' => ['required', 'string', 'max:20'],
            'pict_url' => ['required', 'string', 'max:256'],
        ]);

        if ($validator->fails()) {
            return ['success' => 0, 'result' => $validator->errors()->first(), 'validator' => $validator->errors()];
        }

        $data = $request->only(
            'game_id',
            'game_type',
            'method_name',
            'pict_url',
        );

        $before = NodeEntrance::select(
            'id',
            'game_id',
            'game_type',
            'method_name',
            'pict_url',
        )->where('id', $id)->first();
        NodeEntrance::where('id', $id)->update($data);
        $after = NodeEntrance::select(
            'id',
            'game_id',
            'game_type',
            'method_name',
            'pict_url',
        )->where('id', $id)->first();
        $collection = collect($before);
        $diffItems = $collection->diff($after);
        if (!$diffItems->isEmpty()) {
            ActionLog::create([
                'admin_id' => $this->admin->getLoginID($request),
                'admin_username' => $this->admin->getLoginUsername($request),
                'browser' => $request->header('User-Agent'),
                'key' => 'SERVER_PLAY_EDIT',
                'is_success' => 1,
                'url' => $request->url(),
                'ip' => $this->ip($request),
                'desc' => $before->method_name,
                'target_id' => $before->id,
                'before' => $before->toJson(),
                'after' =>  $after->toJson(),
                'params' => json_encode($request->all()),
                'method' => $request->method()
            ]);
        }
        return $this->success(__('ts.update success'));
    }

    public function playAdd(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'game_id' => ['required', 'integer'],
            'game_type' => ['required', 'string', 'max:50'],
            'method_name' => ['required', 'string', 'max:20'],
            'pict_url' => ['required', 'string', 'max:256'],
        ]);

        if ($validator->fails()) {
            return ['success' => 0, 'result' => $validator->errors()->first(), 'validator' => $validator->errors()];
        }

        $data = $request->only(
            'game_id',
            'game_type',
            'method_name',
            'pict_url',
        );
        $after = NodeEntrance::create($data);
        ActionLog::create([
            'admin_id' => $this->admin->getLoginID($request),
            'admin_username' => $this->admin->getLoginUsername($request),
            'browser' => $request->header('User-Agent'),
            'key' => 'SERVER_PLAY_CREATE',
            'is_success' => 1,
            'url' => $request->url(),
            'ip' => $this->ip($request),
            'desc' => $after->method_name,
            'target_id' => $after->id,
            'after' =>  $after->toJson(),
            'params' => json_encode($request->all()),
            'method' => $request->method()
        ]);
        return $this->success(__('ts.create success'));
    }

    public function processControlView(Request $request)
    {
        return view('GM/Server/processControlView', ['pageTitle' => $this->role->getCurrentPageTitle($request)]);
    }

    public function processControlList(Request $request)
    {
        $limit = $request->query->get('limit', 20);
        $offset = $request->query->get('offset', 0);
        $sort = $request->query->get('sort', 'id');
        $order = $request->query->get('order', 'desc');
        $gameID = $request->query->get('game_id');


        $model = new ConfigGame();
        !empty($sort) && $model = $model->orderBy($sort, $order);
        $gameID && $model = $model->where('admin_username', $gameID);

        $model = $model->select(
            'id',
            'game_id',
            'game_type',
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

    public function processControlDetail(Request $request, $id)
    {
        $data = ConfigGame::select(
            'id',
            'game_id',
            'game_type',
            'xml',
        )
            ->where('id', $id)->first();
        return ['success' => 1, 'data' => $data];
    }

    public function ProcessControlDel(Request $request, $id)
    {
        $before = ConfigGame::select(
            'id',
            'game_id',
            'game_type',
            'xml',
        )->where('id', $id)->first();
        if (empty($before)) {
            return ['success' => 0, 'result' => __('ts.id error')];
        }
        ConfigGame::where('id', $id)->delete();
        ActionLog::create([
            'admin_id' => $this->admin->getLoginID($request),
            'admin_username' => $this->admin->getLoginUsername($request),
            'browser' => $request->header('User-Agent'),
            'key' => 'SERVER_PROCESS_CONTROL_DELETE',
            'is_success' => 1,
            'url' => $request->url(),
            'ip' => $this->ip($request),
            'desc' => $before->name,
            'target_id' => $before->id,
            'before' => $before->toJson(),
            'params' => json_encode($request->all()),
            'method' => $request->method()
        ]);
        return ['success' => 1, 'result' => __('ts.delete success')];
    }

    public function ProcessControlEdit(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'game_id' => ['required', 'integer'],
            'game_type' => ['required', 'integer'],
            'xml' => ['string', new XML()],
        ]);

        if ($validator->fails()) {
            return ['success' => 0, 'result' => $validator->errors()->first(), 'validator' => $validator->errors()];
        }

        $data = $request->only(
            'game_id',
            'game_type',
            'xml',
        );

        $before = ConfigGame::select(
            'id',
            'game_id',
            'game_type',
            'xml',
        )->where('id', $id)->first();
        ConfigGame::where('id', $id)->update($data);
        $after = ConfigGame::select(
            'id',
            'game_id',
            'game_type',
            'xml',
        )->where('id', $id)->first();
        $collection = collect($before);
        $diffItems = $collection->diff($after);
        if (!$diffItems->isEmpty()) {
            ActionLog::create([
                'admin_id' => $this->admin->getLoginID($request),
                'admin_username' => $this->admin->getLoginUsername($request),
                'browser' => $request->header('User-Agent'),
                'key' => 'SERVER_PROCESS_CONTROL_EDIT',
                'is_success' => 1,
                'url' => $request->url(),
                'ip' => $this->ip($request),
                'desc' => $before->game_id,
                'target_id' => $before->id,
                'before' => $before->toJson(),
                'after' =>  $after->toJson(),
                'params' => json_encode($request->all()),
                'method' => $request->method()
            ]);
        }

        $server = new Server();
        $server->processControlXmlPush($after->game_id, $after->game_type);
        return $this->success(__('ts.update success'));
    }

    public function processControlAdd(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'game_id' => ['required', 'integer'],
            'game_type' => ['required', 'integer'],
            'xml' => ['string', new XML()],
        ]);

        if ($validator->fails()) {
            return ['success' => 0, 'result' => $validator->errors()->first(), 'validator' => $validator->errors()];
        }

        $data = $request->only(
            'game_id',
            'game_type',
            'xml',
        );
        $after = ConfigGame::create($data);
        ActionLog::create([
            'admin_id' => $this->admin->getLoginID($request),
            'admin_username' => $this->admin->getLoginUsername($request),
            'browser' => $request->header('User-Agent'),
            'key' => 'SERVER_PROCESS_CONTROL_CREATE',
            'is_success' => 1,
            'url' => $request->url(),
            'ip' => $this->ip($request),
            'desc' => $after->game_id,
            'target_id' => $after->id,
            'after' =>  $after->toJson(),
            'params' => json_encode($request->all()),
            'method' => $request->method()
        ]);

        $server = new Server();
        $server->processControlXmlPush($after->game_id, $after->game_type);
        return $this->success(__('ts.create success'));
    }

    public function maintenanceView(Request $request)
    {
        return view('GM/Server/maintenanceView', ['pageTitle' => $this->role->getCurrentPageTitle($request)]);
    }

    public function maintenanceEdit(Request $request)
    {
        $data = [];
        $gameIds = [];
        $params = $request->all();
        foreach ($params ?? [] as $key => $val) {
            $k = explode('-', $key);
            if (count($k) != 3) {
                continue;
            }
            $plat = $k[1];
            $gameIds[$plat] = $gameIds[$plat] ?? [];
            $gameIds[$plat][] = $k[2];
        }

        foreach ($gameIds ?? [] as $k => $v) {
            $data[] = ['plat' => $k, 'gameid' => $v];
        }
        // $data = json_encode($data);
        // dd($gameIds, $data);
        $before = ConfigAttribute::where('v_key_name', 'GAME_STATUS')->first();

        ConfigAttribute::where('v_key_name', 'GAME_STATUS')->update([
            't_key_value' => json_encode($data)
        ]);

        $before = $before->t_key_value;
        $after = ConfigAttribute::where('v_key_name', 'GAME_STATUS')->first();
        $after = $after->t_key_value;

        if ($before != $after) {
            ActionLog::create([
                'admin_id' => $this->admin->getLoginID($request),
                'admin_username' => $this->admin->getLoginUsername($request),
                'browser' => $request->header('User-Agent'),
                'key' => 'SERVER_MAINTENANCE_EDIT',
                'is_success' => 1,
                'url' => $request->url(),
                'ip' => $this->ip($request),
                'desc' => 'GAME_STATUS',
                'target_id' => 0,
                'before' => $before,
                'after' =>  $after,
                'params' => json_encode($request->all()),
                'method' => $request->method()
            ]);
        }

        $server = new Server();
        $server->refreshMaintenanceConfig($data);
        return $this->success(__('ts.update success'));
    }

    public function maintenanceList(Request $request)
    {
        $model = ConfigAttribute::where('v_key_name', 'GAME_STATUS')->first();
        $gameStatus = json_decode($model->t_key_value, true);

        $gameAlias = config('gm.game_alias');
        $rows = [];
        foreach ($gameAlias as $gameId => $val) {
            $rows[] = ['gameid' => $gameId, 'name' => $val['name']];
        }
        return [
            'result' => [],
            'rows' => $rows,
            'gameStatus' => (array) $gameStatus,
            'success' => 1,
            'total' => 999,
        ];
    }
}
