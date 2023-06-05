<?php

namespace App\Http\Controllers\GM;


use App\Http\Controllers\Controller;
use App\Models\Manager\Role;
use Illuminate\Http\Request;
use App\Models\Manager\Admin;
use App\Models\Manager\ActionLog;
use App\Models\Manager\LoginLog;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
// use PHPGangsta\GoogleAuthenticator;

use App\Http\Library\PHPGangsta_GoogleAuthenticator;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class HomeController extends GMController
{
    public function index(Request $request)
    {
        $menu = $this->role->getCurrentUserMenu($request);
        return view('GM/Home/index2', [
            'menu' => $menu,
            'urlBasic' => '',
            'username' => $this->admin->getLoginUsername($request),
            'proj' => $this->proj,
        ]);
    }

    public function login(Request $request)
    {
        // echo Hash::make('a12345678');exit;
        return view('GM/Home/login2', ['proj' => $this->proj]);
    }

    public function doLogin(Request $request)
    {

        $admin = $this->admin;

        $validator = Validator::make($request->all(), [
            'username' => ['required', 'string', 'min:4', 'max:32', 'alpha_dash'],
            'password' => ['required', 'string', 'max:32', Password::min(8)],
        ]);

        if ($validator->fails()) {
            return $this->redirectPage(false, $validator->errors()->first(), '/login');
        }

        $username = $request->input('username');
        $password = $request->input('password');
        $googleCaptchaCode = $request->input('googleCaptchaCode');
        $res = $admin->loginIn($request, new $this->classMaps['Admin'], new $this->classMaps['LoginLog'], new $this->classMaps['Role'], $username, $password, $googleCaptchaCode);

        if ($res) {
            return $this->redirectPage(true, 'login successed', '/');
        } else {
            return $this->redirectPage(false, $admin->getDesc(), '/login');
        }
    }

    public function doLoginout(Request $request)
    {
        $this->admin->loginOut($request);
        return $this->redirectPage(true, 'login out', '/login');
    }

    public function getBaseData(Request $request)
    {
        $requireItems = $request->query->get('requireItems', '');
        $requireItems = explode(',', $requireItems);
        $return = [];
        $selectItems = config($this->baseDataPath);
        foreach ($requireItems as $key) {
            if (!empty($selectItems[$key])) {

                $rd = $selectItems[$key];
                if (is_callable($selectItems[$key])) {
                    $rd = $selectItems[$key]();
                }

                if (!isset($rd[0]['key'])) {
                    $vs = [];
                    foreach ($rd as $k => $v) {
                        $vs[] = [
                            'key' => (string) $k,
                            'value' => __((string) $v),
                        ];
                    }
                    $return[$key] = $vs;
                } else {
                    $vs = [];
                    if (is_array($rd)) {
                        foreach ($rd as $k => $v) {
                            $v['value'] = __((string) $v['value']);
                            $vs[] = $v;
                        }
                    }
                    $return[$key] = $vs;
                }
            } else {
                throw new \InvalidArgumentException;
            }
        }
        return ['result' => $return];
    }

    public function passwordView(Request $request)
    {
        return view('GM/Home/passwordView');
    }

    public function passwordEdit(Request $request)
    {
        $password = $request->input('password');

        $validator = Validator::make($request->all(), [
            'password' => ['required', 'confirmed', 'string', 'max:32', Password::min(8)],
        ]);

        if ($validator->fails()) {
            return $this->redirectPage(false, $validator->errors()->first(), '/manager/account/password/view');
        }

        $this->classMaps['Admin']::where('id', $this->admin->getLoginID($request))->update([
            'last_update_password_time' => date('Y-m-d H:i:s'),
            'password' => Hash::make($password),
        ]);

        $this->classMaps['ActionLog']::create([
            'admin_id' => $this->admin->getLoginID($request),
            'admin_username' => $this->admin->getLoginUsername($request),
            'browser' => $request->header('User-Agent'),
            'key' => 'MANAGER_ADMIN_EDIT_PASSWORD',
            'is_success' => 1,
            'url' => $request->url(),
            'ip' => $this->ip($request),
            'desc' => $this->admin->getLoginUsername($request),
            'target_id' => $this->admin->getLoginID($request),
            'method' => $request->method()
        ]);

        $admin = new $this->classMaps['Admin']();
        $admin->loginOut($request);
        return $this->redirectPage(true, 'change password successed, please try login!', '/login');
    }

    public function googleCodeView(Request $request)
    {
        $ga = new PHPGangsta_GoogleAuthenticator();

        $username = $this->admin->getLoginUsername($request);

        $secret = $request->session()->get($this->admin->tag . 'googleSecret');

        if (empty($secret)) {
            $secret = $ga->createSecret();
            session([
                $this->admin->tag . 'googleSecret' => $secret
            ]);
        }

        // dd($secret);

        $name = $username . '@' . $request->server('HTTP_HOST');
        $googleCaptcha = $ga->getQRCodeGoogleUrl($name, $secret, $title = null, $params = []);
        return view('GM/Home/googleCodeView', ['googleCaptcha' => $googleCaptcha, 'secret' => $secret]);
    }

    public function googleCodeEdit(Request $request)
    {
        $googleCode = $request->input('googleCode');
        $validator = Validator::make($request->all(), [
            'googleCode' => ['required', 'integer'],
        ]);

        if ($validator->fails()) {
            return $this->redirectPage(false, $validator->errors()->first(), '/manager/account/googlecode/view');
        }

        $secret = $request->session()->get($this->admin->tag . 'googleSecret');
        $ga = new PHPGangsta_GoogleAuthenticator();
        $checkCode = $ga->verifyCode($secret, $googleCode, 2);
        // dd($ga, $checkCode, $googleCode);
        if (!$checkCode) {
            return $this->redirectPage(false, "google captcha code is error", '/manager/account/googlecode/view');
        }

        $this->classMaps['Admin']::where('id', $this->admin->getLoginID($request))->update([
            'is_bind_google_code' => 1,
            'google_captcha' => $secret,
            'last_bind_google_code_time' => date('Y-m-d H:i:s'),
        ]);

        $this->classMaps['ActionLog']::create([
            'admin_id' => $this->admin->getLoginID($request),
            'admin_username' => $this->admin->getLoginUsername($request),
            'browser' => $request->header('User-Agent'),
            'key' => 'MANAGER_ADMIN_EDIT_GOOGLECODE',
            'is_success' => 1,
            'url' => $request->url(),
            'ip' => $this->ip($request),
            'desc' => $this->admin->getLoginUsername($request),
            'target_id' => $this->admin->getLoginID($request),
            'method' => $request->method()
        ]);

        $admin = new $this->classMaps['Admin']();
        $request->session()->forget($this->admin->tag . 'secret');
        $admin->loginOut($request);
        return $this->redirectPage(true, 'change password successed, please try login!', '/login');
    }
}
