<?php

namespace App\Models\Manager;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redis;
use App\Http\Library\PHPGangsta_GoogleAuthenticator;
use Illuminate\Http\Request;
use App\Http\Library\Comm;
class Admin extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'id',
        'username',
        'password',
        'nickname',
        'is_bind_google_code',
        'google_captcha',
        'last_login_ip',
        'last_login_time',
        'last_update_password_time',
        'last_bind_google_code_time',
        'err_login_cnt',
        'is_lock',
        'role_id',
        'ip_white',
        'created'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var string[]
     */
    protected $hidden = [];

    protected $table = 'gm_admin';

    
    public $timestamps = false;

    private $desc;

    private $maxErrLoginCnt = 8;

    public $tag = 'GM_';

    protected $connection = 'Master';

    public function getDesc()
    {
        return $this->desc;
    }

    public function loginIn($request, $admin, $loginLog, $role, $username, $password, $googleCaptchaCode)
    {
        $isSuccess = true;
        $this->desc = 'login success';
        // $loginLog = new LoginLog();
        // $admin = new self;
        // $role = new Role();
        $id = 0;
        $adminData = $admin->where('username', $username)->first();

        if (empty($adminData)) {
            $isSuccess = false;
            $this->desc = "username not found";
        }

        if ($isSuccess && $adminData->is_lock == 1) {
            $isSuccess = false;
            $this->desc = "username is locked";
        }

        if ($isSuccess && !Hash::check($password, $adminData->password)) {
            $isSuccess = false;
            $this->desc = "username or password error";

            $adminData->err_login_cnt = $adminData->err_login_cnt + 1;
            if ($adminData->err_login_cnt > $this->maxErrLoginCnt) {
                $adminData->is_lock = 1;
            }

            $adminData->save();
        }

        if ($isSuccess && !empty($adminData->google_captcha)) {
            $ga = new PHPGangsta_GoogleAuthenticator();
            $checkCode = $ga->verifyCode($adminData->google_captcha, $googleCaptchaCode, 2);
            if (!$checkCode) {
                $isSuccess = false;
                $this->desc = "google captcha code is error";

                $adminData->err_login_cnt = $adminData->err_login_cnt + 1;
                if ($adminData->err_login_cnt > $this->maxErrLoginCnt) {
                    $adminData->is_lock = 1;
                }

                $adminData->save();
            }
        }

        if ($isSuccess) {

            $adminData->err_login_cnt = 0;
            $adminData->last_login_time = date('Y-m-d H:i:s');
            $adminData->last_login_ip = Comm::getIP($request);
            $adminData->save();
            $unique = md5(microtime(true) . rand(0, 100000));
            $roleData = $role->select('role_keys')->where('id', $adminData->role_id)->first();
            session([
                $this->tag . 'admin_id' => $adminData->id,
                $this->tag . 'admin_username' => $adminData->username,
                $this->tag . 'is_bind_google_code' => $adminData->is_bind_google_code,
                $this->tag . 'last_update_password_time' => $adminData->last_update_password_time,
                $this->tag . 'role_keys' => empty($roleData) ? [] : (!empty($roleData->role_keys) ? json_decode($roleData->role_keys, true) : []),
                $this->tag . 'role_id' =>  $adminData->role_id,
                $this->tag . 'unique' =>  $unique,
            ]);

            $redis = Redis::connection('cache');
            $redis->setex($this->tag . 'unique:' . $adminData->id, 7 * 86400, $unique);
            $id = $adminData->id;
        }
        $loginLog->create([
            'admin_id' => $id,
            'admin_username' => $username,
            'desc' => $this->desc,
            'browser' => $request->header('User-Agent'),
            'ip' => Comm::getIP($request),
            'is_success' => $isSuccess,
        ]);

        return $isSuccess;
    }

    public function loginOut($request)
    {
        $request->session()->forget([
            $this->tag . 'admin_id',
            $this->tag . 'admin_username',
            $this->tag . 'is_bind_google_code',
            $this->tag . 'last_update_password_time',
            $this->tag . 'role_keys',
            $this->tag . 'role_id',
        ]);
    }

    public function isLogin($request)
    {
        return !empty($request->session()->has($this->tag . 'admin_id')) ? true : false;
    }

    public function isPermission($request, $role)
    {
        $excepts = ['/'];
        $uri = $request->route()->uri;
        if (in_array($uri, $excepts)) {
            return true;
        }

        if ($request->session()->get($this->tag . 'role_id') == Role::SUPER) {
            return true;
        }

        $key = $role->getCurrentKey($request, ['/']);
        if ($key === false) {
            return false;
        }

        $roleKeys = $request->session()->get($this->tag . 'role_keys');
        if (!in_array($key, $roleKeys)) {
            return false;
        }

        return true;
    }

    public function isUniqueLogin($request)
    {
        $id = $this->getLoginID($request);
        $redis = Redis::connection('cache');
        $unique1 = $request->session()->get($this->tag . 'unique');
        $unique2 = $redis->get($this->tag . 'unique:' . $id);

        // if ($unique1 != $unique2) {
        //     dd($unique1, $unique2, $this->tag);
        // }
        return $unique1 == $unique2;
    }

    public function getLoginUsername($request)
    {
        return $request->session()->get($this->tag . 'admin_username');
    }

    public function getLoginID($request)
    {
        return $request->session()->get($this->tag . 'admin_id');
    }

}
