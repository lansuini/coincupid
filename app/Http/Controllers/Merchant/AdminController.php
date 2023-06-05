<?php
namespace App\Http\Controllers\Merchant;
use Illuminate\Http\Request;
use App\Http\Controllers\GM\AdminController as Controller;
use App\Models\Manager\Analysis\Role;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    protected $proj = 'Merchant';
    
    protected $apiPath = '/merchant/';

    protected $classMaps = [
        'Role' => '\App\Models\Manager\Merchant\Role',
        'Admin' => '\App\Models\Manager\Merchant\Admin',
        'LoginLog' => '\App\Models\Manager\Merchant\LoginLog',
        'ActionLog' => '\App\Models\Manager\Merchant\ActionLog',
    ];

    protected function getCurrentPageTitle(Request $request) {
        $role = new Role();
        return $role->getCurrentPageTitle($request);
    }

    public function adminView(Request $request)
    {
        return view('Merchant/Admin/adminView', ['apiPath' => $this->apiPath, 'pageTitle' => $this->getCurrentPageTitle($request)]);
    }

    public function adminList(Request $request)
    {
        $limit = $request->query->get('limit', 20);
        $offset = $request->query->get('offset', 0);
        $sort = $request->query->get('sort', 'id');
        $order = $request->query->get('order', 'desc');
        $username = $request->query->get('username');
        $isBindGoogleCode = $request->query->get('is_bind_google_code');
        $isLock = $request->query->get('is_lock');
        $clientId = $request->query->get('client_id');

        $admin = new $this->classMaps['Admin']();
        !empty($sort) && $admin = $admin->orderBy($sort, $order);
        $username && $admin = $admin->where('username', $username);
        $isBindGoogleCode && $admin = $admin->where('is_bind_google_code', $isBindGoogleCode);
        $isLock && $admin = $admin->where('is_lock', $isLock);
        $clientId && $admin = $admin->where('client_id', $clientId);


        $admin = $admin->select(
            'id',
            'nickname',
            'username',
            'role_id',
            'is_bind_google_code',
            'is_lock',
            'last_login_ip',
            'last_login_time',
            'last_update_password_time',
            'created',
            'client_id'
        );
        $total = $admin->count();
        $rows = $admin->offset($offset)->limit($limit)->get()->toArray();
        return [
            'result' => [],
            'rows' => $rows,
            'success' => 1,
            'total' => $total,
        ];
    }

    public function adminDetail(Request $request, $id)
    {
        $data = $this->admin->select(
            'id',
            'username',
            'role_id',
            'is_bind_google_code',
            'is_lock',
            'last_login_ip',
            'last_update_password_time',
            'err_login_cnt',
            'last_bind_google_code_time',
            'ip_white',
            'nickname',
            'created',
            'client_id'
        )
            ->where('id', $id)->first();
        return ['success' => 1, 'data' => $data];
    }

    public function adminAdd(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nickname' => ['required', 'string', 'max:32'],
            'username' => ['required', 'string', 'min:4', 'max:32', 'unique:' . $this->classMaps['Admin'] . ',username', 'alpha_dash'],
            'password' => ['required', 'string', 'max:32', Password::min(8)],
            'ip_white' => ['string', 'max:2048'],
        ]);

        if ($validator->fails()) {
            return ['success' => 0, 'result' => $validator->errors()->first(), 'validator' => $validator->errors()];
        }

        $data = $request->only('username', 'nickname', 'role_id', 'is_lock', 'ip_white', 'password', 'client_id');
        $data['password'] = Hash::make($data['password']);
        $after = $this->admin->create($data);
        $this->actionLog->create([
            'admin_id' => $this->admin->getLoginID($request),
            'admin_username' => $this->admin->getLoginUsername($request),
            'browser' => $request->header('User-Agent'),
            'key' => strtoupper($this->proj) . '_MANAGER_ADMIN_CREATE',
            'is_success' => 1,
            'url' => $request->url(),
            'ip' => $this->ip($request),
            'desc' => $after->username,
            'target_id' => $after->id,
            'after' =>  $after->toJson(),
            'params' => json_encode($request->only('nickname', 'role_id', 'is_lock', 'ip_white', 'client_id')),
            'method' => $request->method()
        ]);
        return ['success' => 1, 'result' => __('ts.create success')];
    }

    public function adminEdit(Request $request, $id)
    {
        $isInputPassword = !empty($request->input('password'));

        $validator = Validator::make($request->all(), [
            'nickname' => 'required',
            'password' => [Password::min(8)],
        ]);

        if ($validator->fails()) {
            return ['success' => 0, 'result' => $validator->errors()->first(), 'validator' => $validator->errors()];
        }

        $data = $request->only('nickname', 'role_id', 'is_lock', 'is_bind_google_code', 'ip_white', 'password', 'client_id');

        if ($isInputPassword) {
            $data['password'] = Hash::make($data['password']);
            $data['last_update_password_time'] = '';
        }

        if ($data['is_bind_google_code'] == 0) {
            $data['google_captcha'] = '';
        }

        $before = $this->admin->select('id', 'nickname', 'username', 'role_id', 'is_lock', 'is_bind_google_code', 'ip_white', 'client_id')->where('id', $id)->first();
        $this->admin->where('id', $id)->update($data);
        $after = $this->admin->select('id', 'nickname', 'username', 'role_id', 'is_lock', 'is_bind_google_code', 'ip_white', 'client_id')->where('id', $id)->first();
        $collection = collect($before);
        $diffItems = $collection->diff($after);
        if (!$diffItems->isEmpty()) {
            $this->actionLog->create([
                'admin_id' => $this->admin->getLoginID($request),
                'admin_username' => $this->admin->getLoginUsername($request),
                'browser' => $request->header('User-Agent'),
                'key' => strtoupper($this->proj) . '_MANAGER_ADMIN_EDIT',
                'is_success' => 1,
                'url' => $request->url(),
                'ip' => $this->ip($request),
                'desc' => $before->username,
                'target_id' => $before->id,
                'before' => $before->toJson(),
                'after' =>  $after->toJson(),
                // 'params' => json_encode($request->all()),
                'params' => json_encode($request->only('nickname', 'role_id', 'is_lock', 'is_bind_google_code', 'ip_white', 'client_id')),
                'method' => $request->method()
            ]);
        }
        return ['success' => 1, 'result' => __('ts.update success')];
    }

    public function adminDel(Request $request, $id)
    {
        $before = $this->admin->select('id', 'nickname', 'username', 'role_id', 'is_lock', 'is_bind_google_code', 'ip_white', 'client_id')->where('id', $id)->first();
        if (empty($before)) {
            return ['success' => 0, 'result' => __('ts.id error')];
        }
        $this->admin->where('id', $id)->delete();
        $this->actionLog->create([
            'admin_id' => $this->admin->getLoginID($request),
            'admin_username' => $this->admin->getLoginUsername($request),
            'browser' => $request->header('User-Agent'),
            'key' => strtoupper($this->proj) . '_MANAGER_ADMIN_DELETE',
            'is_success' => 1,
            'url' => $request->url(),
            'ip' => $this->ip($request),
            'desc' => $before->username,
            'target_id' => $before->id,
            'before' => $before->toJson(),
            'params' => json_encode($request->all()),
            'method' => $request->method()
        ]);
        return ['success' => 1, 'result' => __('ts.delete success')];
    }
    
}
