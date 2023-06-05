<?php

namespace App\Http\Controllers\Merchant;

use Illuminate\Http\Request;
use App\Models\Manager\Role;
use App\Models\UserEnterOut;
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
use App\Models\Account;
use Illuminate\Support\Facades\Redis;
class UserController extends MerchantController
{
    public function accountView(Request $request)
    {
        return view('Merchant/User/accountView', [
            'pageTitle' => $this->role->getCurrentPageTitle($request),
            'request' => $request
        ]);
    }

    public function accountList(Request $request)
    {
        $limit = $request->query->get('limit', 20);
        $offset = $request->query->get('offset', 0);
        $sort = $request->query->get('sort', 'id');
        $order = $request->query->get('order', 'desc');
        $v = $request->query->get('v');
        $t = $request->query->get('t');
        $t2 = $request->query->get('t2');
        $isRiskUser = $request->query->get('is_risk_user');
        $bannedType = $request->query->get('banned_type');
        $accountType = $request->query->get('account_type');
        $clientId = $this->admin->getCurrent($request)->client_id;
        $created = $request->query->get('created');
        $created = urldecode($created);
        $created = explode(' - ', $created);
        $s  = \DateTime::createFromFormat('m/d/Y', $created[0])->format('Y-m-d 00:00:00');
        $e  = \DateTime::createFromFormat('m/d/Y', $created[1])->format('Y-m-d 23:59:59');

        $redis = Redis::connection();

        $vms = [
            'uid' => 'account.uid',
            'player_name' => 'account.player_name',
            'nickname' => 'account.nickname',
        ];

        $sorts = [
            'uid' => 'account.uid',
            'last_logon_time' => 'account_ext.last_logon_time',
            'created' => 'account.created',
        ];

        $model = new Account();
        !empty($sort) && $model = $model->orderBy($sorts[$sort], $order);
        $isRiskUser && $model = $model->where('account_ext.is_risk_user', $isRiskUser);
        $accountType && $model = $model->where('account.account_type', $accountType);
        $bannedType && $model = $model->where('account.banned_type', $bannedType);
        $clientId && $model = $model->where('account_ext.client_id', $clientId);

        if (empty($v) && $t2 == 'created') {
            $model = $model->where('account.created', '>=', $s);
            $model = $model->where('account.created', '<=', $e);
        } else if (empty($v)) {
            $model = $model->where('account_ext.last_logon_time', '>=', $s);
            $model = $model->where('account_ext.last_logon_time', '<=', $e);
        }
        
        // $model = $model->select('gm_admin.id', 'username', 'gm_role.name', 'is_lock', 'last_login_ip', 'last_update_password_time', 'gm_admin.created');
        // $model = $model->leftjoin('gm_role', 'gm_role.id', '=', 'gm_admin.role_id');
        if (isset($vms[$t]) && !empty($v)) {
            $model = $model->where($vms[$t], $v);
            // dd($vms, $vms[$t], $v);
        }

        $model = $model->select(
            'account.uid',
            'account.player_name',
            'account.nickname',
            // 'account.avatar',
            // 'account.account_type',
            // 'account.banned_time',
            // 'account.banned_type',
            'account.created',
            // 'account_ext.client_id',
            // 'account_ext.is_risk_user',
            'account_ext.last_logon_time',
        );

        $model = $model->leftjoin('account_ext', 'account_ext.uid', '=', 'account.uid');

        $total = $model->count();
        $rows = $model->offset($offset)->limit($limit)->get()->toArray();

        foreach ($rows as $k => $v) {
            $v['balance'] = (int) $redis->hget($v['uid'], 'gold');
            $rows[$k] = $v;
        }

        return [
            'result' => [],
            'rows' => $rows,
            'success' => 1,
            'total' => $total,
            // 's' => $s->format('Y-m-d'),
            // 's1' => $created[0],
        ];
    }

    public function accountDetail(Request $request, $uid)
    {
        $model = new Account();
        $model->where('account.uid', '=', $uid);
        $model = $model->select(
            'account.uid',
            'account.player_name',
            'account.nickname',
            'account.avatar',
            'account.account_type',
            'account.banned_time',
            'account.banned_type',
            'account.created',

            'account_ext.is_risk_user',
            'account_ext.last_logon_time',
        );

        $model = $model->leftjoin('account_ext', 'account_ext.uid', '=', 'account.uid');
        $data = $model->first();

        return $this->success($data);
    }

    public function userEnterExitRoomWinLoseView(Request $request)
    {
        return view('Merchant/User/userEnterExitRoomWinLoseView', [
            'pageTitle' => $this->role->getCurrentPageTitle($request),
            'role' => new Role, 'request' => $request
        ]);
    }

    public function userEnterExitRoomWinLoseList(Request $request)
    {
        $limit = $request->query->get('limit', 20);
        $offset = $request->query->get('offset', 0);
        $sort = $request->query->get('sort', 'id');
        $order = $request->query->get('order', 'desc');
        $playerName = $request->query->get('player_name');
        $nodeId = $request->query->get('node_id');

        $created = $request->query->get('created');
        $created = urldecode($created);
        $created = explode(' - ', $created);

        $s  = \DateTime::createFromFormat('m/d/Y', $created[0])->format('Y-m-d 00:00:00');
        $e  = \DateTime::createFromFormat('m/d/Y', $created[1])->format('Y-m-d 23:59:59');

        $clientId = $this->admin->getCurrent($request)->client_id;

        $model = new UserEnterOut();
        !empty($sort) && $model = $model->orderBy($sort, $order);
        $playerName && $model = $model->where('account.player_name', $playerName);
        $nodeId && $model = $model->where('user_enter_out.nodeid', $nodeId);
        $model = $model->leftjoin('account', 'account.uid', '=', 'user_enter_out.uid');
        $model = $model->where('user_enter_out.client_id', $clientId);
        $model = $model->where('account.client_id', $clientId);
        $model = $model->where('user_enter_out.type', 3);
        $model = $model->where('user_enter_out.result', '!=', 0);
        $model = $model->where('user_enter_out.post_time', '>=', $s);
        $model = $model->where('user_enter_out.post_time', '<=', $e);

        $model = $model->select(
            // 'user_enter_out.id',
            'user_enter_out.post_time',
            // 'user_enter_out.uid',
            // 'user_enter_out.client_id',
            // 'user_enter_out.version',
            // 'user_enter_out.ip',
            // 'user_enter_out.room_id',
            // 'user_enter_out.room_num',
            // 'user_enter_out.type',
            // 'user_enter_out.result',
            'user_enter_out.change_gold',
            'user_enter_out.last_gold',
            // 'user_enter_out.last_bank_gold',
            // 'user_enter_out.now_bank_gold',
            'user_enter_out.now_gold',
            $model->raw('from_unixtime(user_enter_out.enter_time) as enter_time'),
            'user_enter_out.nodeid',
            // 'room.game_bill',

            'account.player_name'
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
}
