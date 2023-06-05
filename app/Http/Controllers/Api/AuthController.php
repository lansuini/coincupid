<?php

namespace App\Http\Controllers\Api;

use App\Http\Library\TIM;
use App\Http\Library\Comm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Validator;
use App\Models\ShortMessageLog;
use App\Models\Account;
use App\Models\AccountInfo;
use App\Models\AccountMobile;
use App\Models\AccountLoginLog;
use App\Models\AccountAnonymous;
use App\Models\AccountConfig;
use App\Models\AccountCertification;
use App\Models\ShortMessages;
use App\Models\AccountDelete;
use Hashids\Hashids;

use Illuminate\Validation\Rule;

class AuthController extends ApiController
{
    public function sendMobileCaptcha(Request $request)
    {
        $errorCode = null;
        $errorText = null;
        $countryCode = array_keys(config('countryCode'));
        $validator = Validator::make($request->all(), [
            'mobile_area' => ['required', 'max:10', 'min:1', Rule::in($countryCode)],
            'mobile_number' => ['required', 'max:20', 'min:1'],
            'random' => ['required', 'integer', 'min:0', 'max:4294967295'],
            'type' => ['required', 'integer', Rule::in([1, 2, 3])],
            // 'uid' => ['integer'],
        ]);

        if ($validator->fails()) {
            $errorCode = 1010;
            $errorText = 'Data is not legal' . ':' . $validator->errors()->keys()[0] . ' ' . $validator->errors()->first();
        }

        $id = null;

        if ($errorCode === null && $this->requestLimit($request->input('mobile_area') . $request->input('mobile_number'), 'mamuLimit2:', 60, 1) == false) {
            $errorCode = 1013;
            $errorText = 'The verification code is being sent, please wait for 60 seconds before resending';
        }

        if ($errorCode === null && $this->requestLimit($request->input('mobile_area') . $request->input('mobile_number'), 'mamuLimit:', 86400, 10) == false) {
            $errorCode = 1011;
            $errorText = 'SMS sent too many times';
        }

        if ($errorCode === null) {
            $code = rand(0, 9) . rand(0, 9) . rand(0, 9) . rand(0, 9);
            $code = 8888;
            $shortMessage = new ShortMessages();
            $r = $shortMessage->sendCaptcha(
                $request->input('mobile_area'),
                $request->input('mobile_number'),
                $code
            );

            $shortMessageLog = ShortMessageLog::create([
                'short_messages_id' => $r['short_messages_id'],
                'mobile_area' => $request->input('mobile_area'),
                'mobile_number' => $request->input('mobile_number'),
                'code' => $r['symbol'] == 'DEV' ? $code : encrypt($code),
                'ip' => Comm::getIP($request),
                'response' => json_encode($r['response']),
                'is_success' => $r['is_success'],
                'uid' => 0,
                'type' => $request->input('type'),
            ]);

            $hashids = new Hashids(env('SERVER_HASH_IDS_SALT'), 32, env('SERVER_HASH_IDS_STR_TABLE'));
            $id = $hashids->encode($shortMessageLog->id);
        }

        if ($errorCode === null) {
            if ($r['is_success'] == 0) {
                $errorCode = 1012;
                $errorText = "Failed to send verification code";
            }
        }

        if ($errorCode === null) {
            return $this->succ(['message_id' => $id]);
        } else {
            return $this->err($errorCode, $errorText);
        }
    }

    public function loginOut(Request $request)
    {
        $uid = $request->get('uid');
        Account::delToken($uid);
        return $this->succ([]);
    }

    public function loginByMobileAndCaptcha(Request $request)
    {
        $errorCode = null;
        $errorText = null;
        $uid = 0;
        $username = $request->get('mobile_area') . $request->get('mobile_number');
        $desc = '';
        // $countryCode = array_keys(config('countryCode'));
        $validator = Validator::make($request->all(), [
            'mobile_area' => ['required', 'string', 'max:10', 'min:1'],
            'mobile_number' => ['required', 'string', 'max:20', 'min:1'],
            'random' => ['required', 'integer', 'min:0', 'max:4294967295'],
            'message_id' => ['required', 'string', 'max:32', 'min:32'],
            'captcha_code' => ['required', 'string', 'max:4', 'min:4'],
        ]);

        if ($validator->fails()) {
            $errorCode = 1020;
            $errorText = 'Data is not legal' . ':' . $validator->errors()->keys()[0] . ' ' . $validator->errors()->first();
        }

        if ($errorCode === null) {
            $messageId = $request->input('message_id');
            $hashids = new Hashids(env('SERVER_HASH_IDS_SALT'), 32, env('SERVER_HASH_IDS_STR_TABLE'));
            $messageId = $hashids->decode($messageId);
            $messageId = !empty($messageId) ? current($messageId) : 0;

            if ($messageId <= 0) {
                $errorCode = 1021;
                $errorText = 'Data is not legal' . ':' . 'message_id data is error';
            }
        }

        if ($errorCode === null) {
            $shortMessageLog = ShortMessageLog::where('id', $messageId)
                ->where('mobile_area', $request->input('mobile_area'))
                ->where('mobile_number', $request->input('mobile_number'))
                ->first();

            if (empty($shortMessageLog)) {
                $errorCode = 1022;
                $errorText = 'Data is not legal' . ':' . 'message error';
            }
        }

        if ($errorCode === null && $shortMessageLog->is_used == 1) {
            $errorCode = 1026;
            $errorText = 'verification code already used';
        }

        // if ($errorCode === null && time() - strtotime($shortMessageLog->created) > 600) {
        //     $errorCode = 1023;
        //     $errorText = 'verification code expired';
        // }

        if ($errorCode === null) {
            $code = $request->input('captcha_code');
            $oldCode = strlen($shortMessageLog->code) == 4 ? $shortMessageLog->code : decrypt($shortMessageLog->code);
            if ($code != $oldCode) {
                $errorCode = 1024;
                $errorText = 'verification code error';
            }
        }

        if ($errorCode === null) {
            $accountMobile = AccountMobile::where('mobile_area', $request->input('mobile_area'))
                ->where('mobile_number', $request->input('mobile_number'))
                ->first();

            if (empty($accountMobile)) {
                $account = Account::create(['username' => $request->input('mobile_area') . $request->input('mobile_number')]);
                $accountMobile = AccountMobile::create([
                    'uid' => $account->uid,
                    'mobile_area' => $request->input('mobile_area'),
                    'mobile_number' => $request->input('mobile_number')
                ]);
                AccountInfo::create([
                    'uid' => $account->uid,
                ]);
                AccountConfig::create([
                    'uid' => $account->uid,
                    'created_at' => time(),
                ]);
                //人气认证
                AccountCertification::create([
                    'uid' => $account->uid,
                    'created_at' => time(),
                ]);
            } else {
                $account = Account::where(['uid' => $accountMobile->uid])->first();
            }

            $shortMessageLog->is_used = 1;
            $shortMessageLog->update();
            $uid = $account->uid;

            AccountDelete::where('uid', $uid)->update(['is_delete' => 0]);
        }

        if ($errorCode === null) {
            if ($account->is_lock >= 1) {
                $errorCode = 1025;
                $errorText = 'account is locked';
                $desc = 'account is locked';
            }
        }

        if ($errorCode === null) {
            $token = Account::getAndSetToken($account->uid);
            AccountInfo::where('uid', $account->uid)->update([
                'last_login_ip' => Comm::getIP($request),
                'last_login_time' => date('Y-m-d H:i:s'),
            ]);
        }

        if ($uid > 0) {
            AccountLoginLog::create([
                'type' => 0,
                'uid' => $uid,
                'username' => $username,
                'desc' => $errorCode === null ? 'success' : $desc,
                'browser' => $request->header('User-Agent'),
                'ip' => Comm::getIP($request),
                'is_success' => $errorCode === null ? 1 : 0,
            ]);
        }

        if ($errorCode === null) {
            return $this->succ(['token' => $token]);
        } else {
            return $this->err($errorCode, $errorText);
        }
    }

    public function loginByAccountAndPassword(Request $request)
    {
        $errorCode = null;
        $errorText = null;
        $uid = 0;
        $username = $request->get('username');
        $desc = '';
        $maxErrLoginCnt = 10;
        $validator = Validator::make($request->all(), [
            'username' => ['required', 'string', 'max:20', 'min:6'],
            'password' => ['required', 'string', 'max:20', 'min:6'],
            'random' => ['required', 'integer', 'min:0', 'max:4294967295'],
        ]);

        if ($validator->fails()) {
            $errorCode = 1030;
            $errorText = 'Data is not legal' . ':' . $validator->errors()->keys()[0] . ' ' . $validator->errors()->first();
        }

        if ($errorCode === null) {
            $account = Account::where(['username' => $request->input('username')])->first();
            if (empty($account)) {
                $errorCode = 1031;
                $errorText = 'username or password error';
                $desc .= 'username is error';
            } else {
                $uid = $account->uid;
            }
        }

        if ($errorCode === null) {
            if ($account->is_lock >= 1) {
                $errorCode = 1033;
                $errorText = 'account is locked';
                $desc .= 'account is locked';
            }
        }

        if ($errorCode === null) {
            if (empty($account->password) || $request->input('password') != decrypt($account->password)) {
                $errorCode = 1032;
                $errorText = 'username or password error';
                $desc .= 'password is error';

                $account->err_login_cnt = $account->err_login_cnt + 1;
                if ($account->err_login_cnt > $maxErrLoginCnt) {
                    $account->is_lock = 1;
                }

                $account->save();
            }
        }

        if ($errorCode === null) {
            $token = Account::getAndSetToken($account->uid);
            AccountInfo::where('uid', $account->uid)->update([
                'last_login_ip' => Comm::getIP($request),
                'last_login_time' => date('Y-m-d H:i:s'),
            ]);
            $account->err_login_cnt = 0;
            $account->save();

            AccountDelete::where('uid', $uid)->update(['is_delete' => 0]);
        }

        if ($uid > 0) {
            AccountLoginLog::create([
                'type' => 1,
                'uid' => $uid,
                'username' => $username,
                'desc' => $errorCode === null ? $desc . 'success' : $desc,
                'browser' => $request->header('User-Agent'),
                'ip' => Comm::getIP($request),
                'is_success' => $errorCode === null ? 1 : 0,
            ]);
        }

        if ($errorCode === null) {
            return $this->succ(['token' => $token]);
        } else {
            return $this->err($errorCode, $errorText);
        }
    }

    public function setInfo(Request $request)
    {
        $uid = $request->get('uid');
        $errorCode = null;
        $errorText = null;
        $validator = Validator::make($request->all(), [
            'username' => ['string', 'max:20', 'min:6', 'regex:/\w*$/', 'unique:\App\Models\Account,username'],
            'password' => [
                'string', 'max:20', 'min:6',
                'regex:/[a-z]/',      // must contain at least one lowercase letter
                'regex:/[A-Z]/',      // must contain at least one uppercase letter
                'regex:/[0-9]/',      // must contain at least one digit
                'regex:/[@$!%*#?&]/', // must contain a special character],
            ],
            'random' => ['required', 'integer', 'min:0', 'max:4294967295'],
        ]);

        if ($validator->fails()) {
            $errorCode = 1040;
            $errorText = 'Data is not legal' . ':' . $validator->errors()->keys()[0] . ' ' . $validator->errors()->first();
        }

        $updateData = [];
        $password = $request->input('password');
        if ($errorCode === null && !empty($password)) {
            $updateData['password'] = encrypt($password);
        }

        $username = $request->input('username');
        if ($errorCode === null && !empty($username)) {
            $updateData['username'] = $username;
        }

        if ($errorCode === null && !empty($updateData)) {
            Account::where('uid', $uid)->update($updateData);
        }

        if ($errorCode === null) {
            return $this->succ([]);
        } else {
            return $this->err($errorCode, $errorText);
        }
    }

    public function getIMInfo(Request $request)
    {
        $errorCode = null;
        $errorText = null;
        $uid = $request->get('uid');
        $info = AccountInfo::where('uid', $uid)->first();
        $tim = new TIM();
        if (empty($info->im_userid)) {
            $hashids = new Hashids(env('IM_HASH_IDS_SALT'), 8, env('SERVER_HASH_IDS_STR_TABLE'));
            $id = $hashids->encode($uid);
            $info->im_userid = $id;
            $res = $tim->accountImport($id, $uid, '');
            if ($res[0]) {
                $info->update();
            } else {
                $errorCode = 1050;
                $errorText = $res[1];
            }
        }
        $api = new \Tencent\TLSSigAPIv2(env('IM_APPID'), env('IM_KEY'));
        $sig = $api->genUserSig($info->im_userid);

        if ($errorCode === null) {
            $worldGroupId = env('IM_WORLD_GROUPD_ID', 'WorldGroup777');
            $redis = Redis::connection('cache');
            $res = '';
            if (empty($redis->get($worldGroupId))) {
                $res = $tim->createGroup([
                    'GroupId' => $worldGroupId,
                    'Owner_Account' => env('IM_ADMIN'),
                    // "Type" => "Community",
                    // "Type" => "Public",
                    "Type" => "AVChatRoom",
                    "Name" => 'World Group',
                    "Introduction" => 'World Group',
                    "Notification" => 'World Group',
                    "FaceUrl" => '',
                    "ApplyJoinOption" => 'FreeAccess',
                    // "MaxMemberCount" => 10000,
                    "MemberList" => [
                        // ["Member_Account" => $accountInfo->im_userid, "Role" => 'Admin']
                    ],
                ]);

                // if ($res[0]) {
                $redis->setex($worldGroupId, 30 * 86400, 1);
                // }
            }


            return $this->succ(['user_id' => $info->im_userid, 'user_sig' => $sig, 'world_id' => $worldGroupId, 'res' => $res]);
        } else {
            return $this->err($errorCode, $errorText);
        }
    }

    public function loginByAnonymous(Request $request)
    {
        $errorCode = null;
        $errorText = null;
        $uid = 0;
        $username = $request->get('key');
        $desc = '';
        $validator = Validator::make($request->all(), [
            'key' => ['max:13'],
            'random' => ['required', 'integer', 'min:0', 'max:4294967295'],
        ]);

        if ($validator->fails()) {
            $errorCode = 1060;
            $errorText = 'Data is not legal' . ':' . $validator->errors()->keys()[0] . ' ' . $validator->errors()->first();
        }

        if ($errorCode === null && empty($username) && $this->requestLimit(Comm::getIP($request), 'loginByAnonymousLimit:', 86400, 10) == false && env('ANONYMOUS_LOGIN_PROTECT', 1) == 1) {
            $errorCode = 1062;
            $errorText = 'Too many anonymous logins';
        }

        if ($errorCode === null && empty($username)) {
            $username = uniqid();
            $account = Account::create(['username' => $username]);
            AccountInfo::create([
                'uid' => $account->uid,
            ]);
            AccountAnonymous::create([
                'key' => $username,
                'uid' => $account->uid,
            ]);
            AccountConfig::create([
                'uid' => $account->uid,
                'created_at' => time(),
            ]);
            //人气认证
            AccountCertification::create([
                'uid' => $account->uid,
                'created_at' => time(),
            ]);
        }

        if ($errorCode === null && !empty($username)) {
            $account = Account::where(['username' => $username])->first();
            if (empty($account)) {
                $errorCode = 1060;
                $errorText = "key error";
            }
        }

        if ($errorCode === null) {
            if ($account->is_lock >= 1) {
                $errorCode = 1061;
                $errorText = 'account is locked';
                $desc = 'account is locked';
            }
        }

        if ($errorCode === null) {
            $uid = $account->uid;
        }

        if ($errorCode === null) {
            $token = Account::getAndSetToken($account->uid);
            AccountInfo::where('uid', $account->uid)->update([
                'last_login_ip' => Comm::getIP($request),
                'last_login_time' => date('Y-m-d H:i:s'),
            ]);
        }

        if ($uid > 0) {
            AccountLoginLog::create([
                'type' => 2,
                'uid' => $uid,
                'username' => $username,
                'desc' => $errorCode === null ? $desc . 'success' : $desc,
                'browser' => $request->header('User-Agent'),
                'ip' => Comm::getIP($request),
                'is_success' => $errorCode === null ? 1 : 0,
            ]);
        }

        if ($errorCode === null) {
            return $this->succ(['token' => $token, 'key' => $username]);
        } else {
            return $this->err($errorCode, $errorText);
        }
    }

    public function bindMobile(Request $request)
    {
        $errorCode = null;
        $errorText = null;
        $uid = $request->get('uid');
        $validator = Validator::make($request->all(), [
            'mobile_area' => ['required', 'string', 'max:10', 'min:1'],
            'mobile_number' => ['required', 'string', 'max:20', 'min:1'],
            'random' => ['required', 'integer', 'min:0', 'max:4294967295'],
            'message_id' => ['required', 'string', 'max:32', 'min:32'],
            'captcha_code' => ['required', 'string', 'max:4', 'min:4'],
        ]);

        if ($validator->fails()) {
            $errorCode = 1070;
            $errorText = 'Data is not legal' . ':' . $validator->errors()->keys()[0] . ' ' . $validator->errors()->first();
        }

        if ($errorCode === null) {
            $messageId = $request->input('message_id');
            $hashids = new Hashids(env('SERVER_HASH_IDS_SALT'), 32, env('SERVER_HASH_IDS_STR_TABLE'));
            $messageId = $hashids->decode($messageId);
            $messageId = !empty($messageId) ? current($messageId) : 0;

            if ($messageId <= 0) {
                $errorCode = 1071;
                $errorText = 'Data is not legal' . ':' . 'message_id data is error';
            }
        }

        if ($errorCode === null) {
            $shortMessageLog = ShortMessageLog::where('id', $messageId)
                ->where('type', 2)
                ->where('mobile_area', $request->input('mobile_area'))
                ->where('mobile_number', $request->input('mobile_number'))
                ->first();

            if (empty($shortMessageLog)) {
                $errorCode = 1072;
                $errorText = 'Data is not legal' . ':' . 'message error';
            }
        }

        if ($errorCode === null && $shortMessageLog->is_used == 1) {
            $errorCode = 1076;
            $errorText = 'verification code already used';
        }

        // if ($errorCode === null && time() - strtotime($shortMessageLog->created) > 600) {
        //     $errorCode = 1073;
        //     $errorText = 'verification code expired';
        // }

        if ($errorCode === null) {
            $code = $request->input('captcha_code');
            $oldCode = strlen($shortMessageLog->code) == 4 ? $shortMessageLog->code : decrypt($shortMessageLog->code);
            if ($code != $oldCode) {
                $errorCode = 1074;
                $errorText = 'verification code error';
            }
        }

        if ($errorCode === null) {
            $accountMobile = AccountMobile::where('mobile_area', $request->input('mobile_area'))
                ->where('mobile_number', $request->input('mobile_number'))
                ->first();

            if (!empty($accountMobile)) {
                $errorCode = 1075;
                $errorText = 'mobile number already in use';
            }
        }

        if ($errorCode === null) {
            Account::where('uid', $uid)->update(['username' => $request->input('mobile_area') . $request->input('mobile_number')]);

            $accountMobile = AccountMobile::where('uid', $uid)->first();
            if (empty($accountMobile)) {
                AccountMobile::create([
                    'uid' => $uid,
                    'mobile_area' => $request->input('mobile_area'),
                    'mobile_number' => $request->input('mobile_number')
                ]);
            } else {
                $accountMobile->mobile_area = $request->input('mobile_area');
                $accountMobile->mobile_number = $request->input('mobile_number');
                $accountMobile->update();
            }

            AccountAnonymous::where('uid', $uid)->update(['is_bind_mobile' => 1]);

            $shortMessageLog->is_used = 1;
            $shortMessageLog->update();
        }

        if ($errorCode === null) {
            return $this->succ([]);
        } else {
            return $this->err($errorCode, $errorText);
        }
    }

    public function getCountryCode(Request $request)
    {
        $countryCode = config('countryCode');
        return $this->succ($countryCode);
    }

    public function getDeleteAccountStatus(Request $request)
    {
        $uid = $request->get('uid');
        $account = new Account;
        return $this->succ($account->getDeleteAccountStatus($uid));
    }

    public function sendMobileCaptchaByUid(Request $request)
    {
        $errorCode = null;
        $errorText = null;
        $uid = $request->get('uid');
        $accountMobile = new AccountMobile();
        $validator = Validator::make($request->all(), [
            'random' => ['required', 'integer', 'min:0', 'max:4294967295'],
            'type' => ['required', 'integer', Rule::in([4])],
        ]);

        if ($validator->fails()) {
            $errorCode = 1090;
            $errorText = 'Data is not legal' . ':' . $validator->errors()->keys()[0] . ' ' . $validator->errors()->first();
        }

        $id = null;
        if ($errorCode === null && $this->requestLimit($uid, 'mamuLimit2:', 60, 1) == false) {
            $errorCode = 1093;
            $errorText = 'The verification code is being sent, please wait for 60 seconds before resending';
        }

        if ($errorCode === null && $this->requestLimit($uid, 'mamuLimit:', 86400, 10) == false) {
            $errorCode = 1091;
            $errorText = 'SMS sent too many times';
        }

        if ($errorCode === null) {
            $accountMobileData = $accountMobile->where('uid', $uid)->first();
            $code = rand(0, 9) . rand(0, 9) . rand(0, 9) . rand(0, 9);
            $code = 8888;
            $shortMessage = new ShortMessages();
            $r = $shortMessage->sendCaptcha(
                $accountMobileData->mobile_area,
                $accountMobileData->mobile_number,
                $code
            );

            $shortMessageLog = ShortMessageLog::create([
                'short_messages_id' => $r['short_messages_id'],
                'mobile_area' => $accountMobileData->mobile_area,
                'mobile_number' => $accountMobileData->mobile_number,
                'code' => $r['symbol'] == 'DEV' ? $code : encrypt($code),
                'ip' => Comm::getIP($request),
                'response' => json_encode($r['response']),
                'is_success' => $r['is_success'],
                'uid' => $uid,
                'type' => $request->input('type'),
            ]);

            $hashids = new Hashids(env('SERVER_HASH_IDS_SALT'), 32, env('SERVER_HASH_IDS_STR_TABLE'));
            $id = $hashids->encode($shortMessageLog->id);
        }

        if ($errorCode === null) {
            if ($r['is_success'] == 0) {
                $errorCode = 1092;
                $errorText = "Failed to send verification code";
            }
        }

        if ($errorCode === null) {
            return $this->succ(['message_id' => $id]);
        } else {
            return $this->err($errorCode, $errorText);
        }
    }

    public function deleteAccount(Request $request)
    {
        $errorCode = null;
        $errorText = null;
        $uid = $request->get('uid');
        $validator = Validator::make($request->all(), [
            'random' => ['required', 'integer', 'min:0', 'max:4294967295'],
            'message_id' => ['required', 'string', 'max:32', 'min:32'],
            'captcha_code' => ['required', 'string', 'max:4', 'min:4'],
        ]);

        if ($validator->fails()) {
            $errorCode = 1080;
            $errorText = 'Data is not legal' . ':' . $validator->errors()->keys()[0] . ' ' . $validator->errors()->first();
        }

        if ($errorCode === null) {
            $messageId = $request->input('message_id');
            $hashids = new Hashids(env('SERVER_HASH_IDS_SALT'), 32, env('SERVER_HASH_IDS_STR_TABLE'));
            $messageId = $hashids->decode($messageId);
            $messageId = !empty($messageId) ? current($messageId) : 0;

            if ($messageId <= 0) {
                $errorCode = 1081;
                $errorText = 'Data is not legal' . ':' . 'message_id data is error';
            }
        }

        if ($errorCode === null) {
            $shortMessageLog = ShortMessageLog::where('id', $messageId)
                ->where('type', 4)
                ->where('uid', $uid)
                ->first();

            if (empty($shortMessageLog)) {
                $errorCode = 1082;
                $errorText = 'Data is not legal' . ':' . 'message error';
            }
        }

        if ($errorCode == null) {
            $uid = $request->get('uid');
            $account = new Account;
            $accountMobile = new AccountMobile;
            $accountDelete = new accountDelete;
            $status = $account->getDeleteAccountStatus($uid);
            foreach ($status as $s) {
                if ($s == 0) {
                    $errorCode = 1081;
                    $errorText = "account status is not allow delete";
                    break;
                }
            }
        }

        if ($errorCode === null) {
            $accountData = $account->where('uid', $uid)->first();
            $accountMobileData = $accountMobile->where('uid', $uid)->first();
            $accountDeleteData = $accountDelete->where('uid', $uid)->first();

            if (empty($accountDeleteData)) {
                AccountDelete::create([
                    'uid' => $uid,
                    'is_delete' => 1,
                    'username' => $accountData->username,
                    'mobile_area' => $accountMobileData->mobile_area,
                    'mobile_number' => $accountMobileData->mobile_number,
                    'delete_time' => date('Y-m-d H:i:s'),
                ]);
            } else {
                AccountDelete::where('uid', $uid)->update([
                    'is_delete' => 1,
                    'username' => $accountData->username,
                    'mobile_area' => $accountMobileData->mobile_area,
                    'mobile_number' => $accountMobileData->mobile_number,
                    'delete_time' => date('Y-m-d H:i:s'),
                ]);
            }
        }

        if ($errorCode === null) {
            return $this->succ([]);
        } else {
            return $this->err($errorCode, $errorText);
        }
    }
}
