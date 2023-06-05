<?php

namespace App\Http\Controllers\GM;

use Illuminate\Http\Request;
// use App\Models\Manager\Role;
// use App\Models\Manager\Admin;
// use App\Models\Manager\LoginLog;
// use App\Models\Manager\ActionLog;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\App;
use Illuminate\Session;

class AdminController extends GMController
{
    protected $classMaps = [
        'Role' => '\App\Models\Manager\Role',
        'Admin' => '\App\Models\Manager\Admin',
        'LoginLog' => '\App\Models\Manager\LoginLog',
        'ActionLog' => '\App\Models\Manager\ActionLog',
    ];

    protected function getCurrentPageTitle(Request $request)
    {
        return $this->role->getCurrentPageTitle($request);
    }

    public function adminView(Request $request)
    {
        return view('GM/Admin/adminView', ['apiPath' => $this->apiPath, 'pageTitle' => $this->getCurrentPageTitle($request)]);
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

        $admin = new $this->classMaps['Admin']();
        !empty($sort) && $admin = $admin->orderBy($sort, $order);
        $username && $admin = $admin->where('username', $username);
        $isBindGoogleCode && $admin = $admin->where('is_bind_google_code', $isBindGoogleCode);
        $isLock && $admin = $admin->where('is_lock', $isLock);
        // $admin = $admin->select('gm_admin.id', 'username', 'gm_role.name', 'is_lock', 'last_login_ip', 'last_update_password_time', 'gm_admin.created');
        // $admin = $admin->leftjoin('gm_role', 'gm_role.id', '=', 'gm_admin.role_id');

        $admin = $admin->select('id', 'nickname', 'username', 'role_id', 'is_bind_google_code', 'is_lock', 'last_login_ip', 'last_login_time', 'last_update_password_time', 'created');
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
            'created'
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

        $data = $request->only('username', 'nickname', 'role_id', 'is_lock', 'ip_white', 'password');
        $data['password'] = Hash::make($data['password']);
        $after = $this->admin->create($data);
        $this->actionLog->create([
            'admin_id' => $this->admin->getLoginID($request),
            'admin_username' => $this->admin->getLoginUsername($request),
            'browser' => $request->header('User-Agent'),
            'key' => strtoupper($this->proj) . 'MANAGER_ADMIN_CREATE',
            'is_success' => 1,
            'url' => $request->url(),
            'ip' => $this->ip($request),
            'desc' => $after->username,
            'target_id' => $after->id,
            'after' =>  $after->toJson(),
            'params' => json_encode($request->only('nickname', 'role_id', 'is_lock', 'ip_white')),
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

        $data = $request->only('nickname', 'role_id', 'is_lock', 'is_bind_google_code', 'ip_white', 'password');

        if ($isInputPassword) {
            $data['password'] = Hash::make($data['password']);
            $data['last_update_password_time'] = '';
        }

        if ($data['is_bind_google_code'] == 0) {
            $data['google_captcha'] = '';
        }

        $before = $this->admin->select('id', 'nickname', 'username', 'role_id', 'is_lock', 'is_bind_google_code', 'ip_white')->where('id', $id)->first();
        $this->admin->where('id', $id)->update($data);
        $after = $this->admin->select('id', 'nickname', 'username', 'role_id', 'is_lock', 'is_bind_google_code', 'ip_white')->where('id', $id)->first();
        $collection = collect($before);
        $diffItems = $collection->diff($after);
        if (!$diffItems->isEmpty()) {
            $this->actionLog->create([
                'admin_id' => $this->admin->getLoginID($request),
                'admin_username' => $this->admin->getLoginUsername($request),
                'browser' => $request->header('User-Agent'),
                'key' => strtoupper($this->proj) . 'MANAGER_ADMIN_EDIT',
                'is_success' => 1,
                'url' => $request->url(),
                'ip' => $this->ip($request),
                'desc' => $before->username,
                'target_id' => $before->id,
                'before' => $before->toJson(),
                'after' =>  $after->toJson(),
                // 'params' => json_encode($request->all()),
                'params' => json_encode($request->only('nickname', 'role_id', 'is_lock', 'is_bind_google_code', 'ip_white')),
                'method' => $request->method()
            ]);
        }
        return ['success' => 1, 'result' => __('ts.update success')];
    }

    public function adminDel(Request $request, $id)
    {
        $before = $this->admin->select('id', 'nickname', 'username', 'role_id', 'is_lock', 'is_bind_google_code', 'ip_white')->where('id', $id)->first();
        if (empty($before)) {
            return ['success' => 0, 'result' => __('ts.id error')];
        }
        $this->admin->where('id', $id)->delete();
        $this->actionLog->create([
            'admin_id' => $this->admin->getLoginID($request),
            'admin_username' => $this->admin->getLoginUsername($request),
            'browser' => $request->header('User-Agent'),
            'key' => strtoupper($this->proj) . 'MANAGER_ADMIN_DELETE',
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

    public function roleView(Request $request)
    {
        return view('GM/Admin/roleView', ['apiPath' => $this->apiPath, 'pageTitle' => $this->getCurrentPageTitle($request)]);
    }

    public function roleList(Request $request)
    {
        $limit = $request->query->get('limit', 20);
        $offset = $request->query->get('offset', 0);
        $sort = $request->query->get('sort', 'id');
        $order = $request->query->get('order', 'desc');
        $name = $request->query->get('name');

        $model = new $this->classMaps['Role']();
        !empty($sort) && $model = $model->orderBy($sort, $order);
        $name && $model = $model->where('name', $name);
        
        $model = $model->select('id', 'name', 'created');
        $total = $model->count();
        $rows = $model->offset($offset)->limit($limit)->get()->toArray();
        return [
            'result' => [],
            'rows' => $rows,
            'success' => 1,
            'total' => $total,
        ];
    }

    public function roleDetail(Request $request, $id)
    {
        $data = $this->role->select(
            'id',
            'name',
            'role_keys',
            'created'
        )
            ->where('id', $id)->first();
        $config = config($this->role->getMenuPath());
        foreach ($config as $k => $v) {
            if (isset($v['name'])) {
                $v['name'] = __($v['name']);
            }

            foreach ($v['sub_menu_list'] ?? [] as $kk => $vv) {
                if (isset($vv['name'])) {
                    $vv['name'] = __($vv['name']);
                }
                $v['sub_menu_list'][$kk] = $vv;
            }
            $config[$k] = $v;
        }
        return ['success' => 1, 'data' => $data, 'menu' => $config];
    }

    public function RoleDel(Request $request, $id)
    {
        $before = $this->role->select('id', 'name', 'role_keys', 'created')->where('id', $id)->first();
        if (empty($before)) {
            return ['success' => 0, 'result' => __('ts.id error')];
        }
        $this->role->where('id', $id)->delete();
        $this->actionLog->create([
            'admin_id' => $this->admin->getLoginID($request),
            'admin_username' => $this->admin->getLoginUsername($request),
            'browser' => $request->header('User-Agent'),
            'key' => strtoupper($this->proj) . 'MANAGER_ROLE_DELETE',
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

    public function RoleEdit(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'role_keys' => 'json',
        ]);

        if ($validator->fails()) {
            return ['success' => 0, 'result' => $validator->errors()->first(), 'validator' => $validator->errors()];
        }

        $data = $request->only('name', 'role_keys');

        $before = $this->role->select('id', 'name', 'role_keys', 'created')->where('id', $id)->first();
        $this->role->where('id', $id)->update($data);
        $after = $this->role->select('id', 'name', 'role_keys', 'created')->where('id', $id)->first();
        $collection = collect($before);
        $diffItems = $collection->diff($after);
        if (!$diffItems->isEmpty()) {
            $this->actionLog->create([
                'admin_id' => $this->admin->getLoginID($request),
                'admin_username' => $this->admin->getLoginUsername($request),
                'browser' => $request->header('User-Agent'),
                'key' => strtoupper($this->proj) . 'MANAGER_ROLE_EDIT',
                'is_success' => 1,
                'url' => $request->url(),
                'ip' => $this->ip($request),
                'desc' => $before->name,
                'target_id' => $before->id,
                'before' => $before->toJson(),
                'after' =>  $after->toJson(),
                'params' => json_encode($request->all()),
                'method' => $request->method()
            ]);
        }
        return ['success' => 1, 'result' => __('ts.update success')];
    }

    public function roleAdd(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:32', 'unique:' . $this->classMaps['Role'] . ',name'],
        ]);

        if ($validator->fails()) {
            return ['success' => 0, 'result' => $validator->errors()->first(), 'validator' => $validator->errors()];
        }

        $data = $request->only('name');
        $after = $this->role->create($data);
        $this->actionLog->create([
            'admin_id' => $this->admin->getLoginID($request),
            'admin_username' => $this->admin->getLoginUsername($request),
            'browser' => $request->header('User-Agent'),
            'key' => strtoupper($this->proj) . 'MANAGER_ROLE_CREATE',
            'is_success' => 1,
            'url' => $request->url(),
            'ip' => $this->ip($request),
            'desc' => $after->name,
            'target_id' => $after->id,
            'after' =>  $after->toJson(),
            'params' => json_encode($request->only('nickname', 'role_id', 'is_lock', 'ip_white')),
            'method' => $request->method()
        ]);
        return ['success' => 1, 'result' => __('ts.create success')];
    }

    public function loginLogView(Request $request)
    {
        return view('GM/Admin/loginLogView', ['apiPath' => $this->apiPath, 'pageTitle' => $this->getCurrentPageTitle($request)]);
    }

    public function loginLogList(Request $request)
    {
        $limit = $request->query->get('limit', 20);
        $offset = $request->query->get('offset', 0);
        $sort = $request->query->get('sort', 'id');
        $order = $request->query->get('order', 'desc');
        $adminUsername = $request->query->get('admin_username');
        $isSuccess = $request->query->get('is_success', -1);
        $created = $request->query->get('created');
        $created = urldecode($created);
        $created = explode(' - ', $created);
        $s  = \DateTime::createFromFormat('m/d/Y', $created[0])->format('Y-m-d 00:00:00');
        $e  = \DateTime::createFromFormat('m/d/Y', $created[1])->format('Y-m-d 23:59:59');
        $model = new $this->classMaps['LoginLog']();
        !empty($sort) && $model = $model->orderBy($sort, $order);
        $adminUsername && $model = $model->where('admin_username', $adminUsername);
        $isSuccess != -1 && $model = $model->where('is_success', $isSuccess);
        $model = $model->where('created', '>=', $s);
        $model = $model->where('created', '<=', $e);
        $model = $model->select(
            'id',
            'admin_id',
            'admin_username',
            'browser',
            'is_success',
            'desc',
            'ip',
            'created'
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


    public function actionLogView(Request $request)
    {
        return view('GM/Admin/actionLogView', ['apiPath' => $this->apiPath, 'pageTitle' => $this->getCurrentPageTitle($request)]);
    }

    public function actionLogList(Request $request)
    {
        $limit = $request->query->get('limit', 20);
        $offset = $request->query->get('offset', 0);
        $sort = $request->query->get('sort', 'id');
        $order = $request->query->get('order', 'desc');
        $adminUsername = $request->query->get('admin_username');
        $key = $request->query->get('key');
        $created = $request->query->get('created');
        $created = urldecode($created);
        $created = explode(' - ', $created);
        $s  = \DateTime::createFromFormat('m/d/Y', $created[0])->format('Y-m-d 00:00:00');
        $e  = \DateTime::createFromFormat('m/d/Y', $created[1])->format('Y-m-d 23:59:59');
        $model = new $this->classMaps['ActionLog']();
        !empty($sort) && $model = $model->orderBy($sort, $order);
        $adminUsername && $model = $model->where('admin_username', $adminUsername);
        $key && $model = $model->where('key', $key);
        $model = $model->where('created', '>=', $s);
        $model = $model->where('created', '<=', $e);
        $model = $model->select(
            'id',
            'admin_username',
            'key',
            'is_success',
            'method',
            'desc',
            'created'
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

    public function actionLogDetail(Request $request, $id)
    {
        $data = $this->actionLog->select(
            'id',
            'admin_id',
            'admin_username',
            'browser',
            'key',
            'before',
            'after',
            'target_id',
            'ip',
            'is_success',
            'url',
            'method',
            'params',
            'desc',
            'created'
        )
            ->where('id', $id)->first();
        return ['success' => 1, 'data' => $data];
    }

    public function setLang(Request $request, $lang)
    {
        App::setLocale($lang);
        $request->session()->put('language', $lang);
        return App::getLocale();
    }
}
