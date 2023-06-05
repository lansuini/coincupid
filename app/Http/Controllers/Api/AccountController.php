<?php
namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Models\AccountInfo;
use App\Models\Account;
use App\Models\AccountCertification;
use Illuminate\Support\Facades\Redis;
use Carbon\Carbon;
use Aws\S3\S3Client;
use Mews\Purifier\Purifier;
use Illuminate\Validation\Rule;
class AccountController extends ApiController
{
    public function resetPassword(Request $request){
        $uid = $request->get('uid');
        $params = $request->post();
        $validator = Validator::make($request->all(), [
//            'password_old' => ['required', 'string', 'max:20', 'min:6'],
            'password_new' => ['required', 'string', 'max:20', 'min:6','confirmed',
                'regex:/[a-z]/',      // must contain at least one lowercase letter
                'regex:/[A-Z]/',      // must contain at least one uppercase letter
                'regex:/[0-9]/',      // must contain at least one digit
                'regex:/[@$!%*#?&]/', // must contain a special character],
            ],
            'password_new_confirmation' => ['required', 'same:password_new'],
            [
//                'password_old.required' => 'The old password is required',
                'password_new.confirmed' => 'The new password repeated is not the same',
            ],
            [
//                'password_old' => '原密码',
                'password_new' => '新密码',
                'password_new_confirmation' => '密码确认',
            ]
        ]);

        if ($validator->fails()) {
            $errorCode = 1030;
            $errorText = 'Data is not legal' . ':' . $validator->errors()->keys()[0] . ' ' . $validator->errors()->first();
            return $this->err($errorCode, $errorText);
        }
        $account = DB::table("account")->where('uid',$uid)->first();
        if(!$account){
            return $this->err(404, "User Not Found");
        }

//        if(encrypt($params['password_old']) != $account->password){
//            $errorCode = 400;
//            $errorText = 'The old password is Incorrect !';
//            return $this->err($errorCode, $errorText);
//        }

        DB::table("account")->where('uid',$uid)->update(['password'=>encrypt($params['password_new'])]);

        return $this->succ([]);
    }

    public function setCoordinate(Request $request)
    {

        $uid = $request->get('uid');
        $params = $request->all();
        Validator::extend('latlong', function ($attribute, $value, $parameters, $validator) {
            //UTF-8汉字字母数字下划线
            $reg = '/^(-)?[0-9]+(\.[0-9]+)?$/';
            //获取字节长度
            $nameLength = strlen($value);
            if ($nameLength >= 1 && $nameLength <= 20 && preg_match($reg, $value)) {
                return true;
            }
            return false;
        });
        $countryCode = array_keys(config('countries'));

        $validator = Validator::make($params, [
            'lat' => ['required', 'string', 'latlong'], //纬度
            'long' => ['required', 'string', 'latlong'], //经度
            'client_ip' => ['ip', 'between:5,20'], //ip
            'country' => ['string', 'between:2,25'], //国家
            'country_code' => ['string', 'between:2,10'], //国家代码
            'province' => ['string', 'between:2,25'], //省份
            'city' => ['string', 'between:2,25'], //城市
        ]);

        if ($validator->fails()) {
            $errorCode = 1070;
            $errorText = 'Data is not legal' . ':' . $validator->errors()->keys()[0] . ' ' . $validator->errors()->first();
            return $this->err($errorCode, $errorText);
        }
        if (!isset($params['client_ip']) || empty($params['client_ip'])) {
            $params['client_ip'] = $request->getClientIp();
        }
        if (isset($params['country_code']) && !empty($params['country_code'])) {
            $params['country_code'] = in_array($params['country_code'], $countryCode) ? $params['country_code'] : '';
        }
        if ($params['country_code']) {
            $cacheKey = 'ever_seen_countries:' . $uid;
            $iset = Redis::sismember($cacheKey, $params['country_code']);
            if (!$iset) {
                Redis::sadd($cacheKey, $params['country_code']);
            }
        }
        $editData = [
            'lat' => $params['lat'], //纬度
            'long' => $params['long'], //经度
            'client_ip' => $params['client_ip'], //ip
            'country_code' => $params['country_code'] ?? '', //国家代码
            'country' => $params['country'] ?? '', //国家
            'province' => $params['province'] ?? '', //省份
            'city' => $params['city'] ?? '', //城市
        ];
        try {
            $res = AccountInfo::where('uid', $uid)->update($editData);
        } catch (\Exception $e) {
            Log::Error("$uid 设置坐标失败：" . $e->getMessage());
            return $this->err(501, "System Error");
        }

        if ($res === false) {
            $errorCode = 400;
            $errorText = "Set Account Failed";
            return $this->err($errorCode, $errorText);
        }
        return $this->succ([]);
    }

    public function getTagAttrs()
    {
        $cacheKey = "user_tag_attrs";
        $cacheData = Redis::get($cacheKey);
        $tagAttrs = [];
        if($cacheData){
            $tagAttrs = json_decode($cacheData, true);
        }
        if (!$tagAttrs) {
            $tagAttrs = DB::table("user_tag_attr")->orderBy("attr_id")->get(['attr_id', 'attr_name', 'display'])->toArray();
            Redis::setex($cacheKey, 60, json_encode($tagAttrs));
        }

        return $this->succ(['list' => $tagAttrs]);
    }

    //添加个人标签
    public function addTag(Request $request)
    {

        $uid = $request->get('uid');
        $hkey = "user_add_tag";
        $addTagCounts = Redis::hget($hkey, $uid);
        if ($addTagCounts >= 10) {
            return $this->err(400, "个人标签超过设置上限");
        }
        $params = $request->all();
        $validator = Validator::make($params, [
            'tag_name' => ['required', 'string', 'between:1,15', 'alpha_dash'],
        ]);

        if ($validator->fails()) {
            $errorCode = 1070;
            $errorText = 'Data is not legal' . ':' . $validator->errors()->keys()[0] . ' ' . $validator->errors()->first();
            return $this->err($errorCode, $errorText);
        }
        $cacheKey = "user_tags";
        $cacheuserTags = Redis::get($cacheKey);
        $userTags = [];
        if($cacheuserTags){
            $userTags = json_decode($cacheuserTags, true);
        }
        if (!$userTags) {
            $userTags = DB::table("user_tag")->pluck("tag_name", "tag_id")->toArray();
        }

        $tagName = clean($request->input("tag_name"));
        foreach ($userTags as $key => $val) {
            if ($tagName == $val) {
                return $this->succ(['tag_id' => $key]);
                break;
            }
        }
        //        if(in_array($tagName,$userTags)){
        //
        //            return $this->succ([]);
        //        }
        $insertData = [
            'tag_name' => $tagName,
            'attr_id' => 1,
            'uid' => $uid,
        ];
        $tagId = DB::table("user_tag")->insertGetId($insertData);
        if (!$tagId) {
            $errorCode = 400;
            $errorText = "Add Tag Failed";
            return $this->err($errorCode, $errorText);
        }
        Redis::hset($hkey, $uid, $addTagCounts + 1);
        $userTags = DB::table("user_tag")->pluck("tag_name", "tag_id")->toArray();
        Redis::setex($cacheKey, 60, json_encode($userTags));
        return $this->succ(['tag_id' => $tagId]);
    }

    //标签列表
    public function getTags(Request $request)
    {
        //         $uid = $request->get('uid');
        $params = $request->all();
        $validator = Validator::make($params, [
            'attr_id' => ['required', 'integer', 'between:1,999999'],
            'page' => ['required', 'integer', 'between:1,99'],
        ]);

        if ($validator->fails()) {
            $errorCode = 1070;
            $errorText = 'Data is not legal' . ':' . $validator->errors()->keys()[0] . ' ' . $validator->errors()->first();
            return $this->err($errorCode, $errorText);
        }
        $cacheKey = "user_tag_attrs";
        $cacheData = Redis::get($cacheKey);
        $tagAttrs = [];
        if($cacheData){
            $tagAttrs = json_decode($cacheData, true);
        }
        if (!$tagAttrs) {
            $tagAttrs = DB::table("user_tag_attr")->where("display", 1)->orderBy("attr_id")->get(['attr_id', 'attr_name'])->toArray();
            Redis::setex($cacheKey, 60, json_encode($tagAttrs));
        }
        $sections = array_column($tagAttrs, 'attr_id');
        $attrId = $request->input("attr_id");
        if (!in_array($attrId, $sections)) {
            return $this->err(400, "Error data");
        }
        $data = [];
        $query = DB::table('user_tag')->where("attr_id", $attrId)
            ->select(['tag_id', 'tag_name', 'attr_id'])
            ->forPage($params['page'], 10);
        $data['list'] = $query->get()->toArray();

        $attributes['total'] = $query->count();
        $attributes['page'] = $params['page'];
        $attributes['page_size'] = 10;
        $data['attributes'] = $attributes;

        return $this->succ($data);
    }

    //设置标签
    public function setTags(Request $request)
    {
        $uid = $request->get('uid');
        $validator = Validator::make($request->all(), [
            'tag_ids' => ['required', 'string', 'between:1,500'],
            //             'tag_ids.*'  => "required|integer|distinct|max:999999|min:1",
        ]);

        if ($validator->fails()) {
            $errorCode = 1070;
            $errorText = 'Data is not legal' . ':' . $validator->errors()->keys()[0] . ' ' . $validator->errors()->first();
            return $this->err($errorCode, $errorText);
        }

        $tagStr = $request->input("tag_ids");
        $tagIds = array_unique(array_filter(explode(',', $tagStr)));
        if (count($tagIds) > 50) {
            return $this->err(400, "设置标签不能大于50个");
        }
        $cacheKey = "user_tags";
        $cacheUserTags = Redis::get($cacheKey);
        $userTags = [];
        if($cacheUserTags){
            $userTags = json_decode($cacheUserTags, true);
        }
        if (!$userTags) {
            $userTags = DB::table("user_tag")->pluck("tag_name", "tag_id")->toArray();
            Redis::setex($cacheKey, 60, json_encode($userTags));
        }
        $insertTags = [];
        foreach ($tagIds as $tagId) {
            $row = [];
            if (!isset($userTags[(int)$tagId])) {
                continue;
            }
            $row['uid'] = $uid;
            $row['tag_id'] = $tagId;
            $row['tag_name'] = $userTags[$tagId];
            array_push($insertTags, $row);
        }
        if (empty($insertTags)) {
            return $this->err(400, "无效标签！");
        }
        try {
            DB::beginTransaction();
            DB::table("account_tag")->where("uid", $uid)->delete();
            DB::table("account_tag")->insert($insertTags);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::Error("$uid 设置标签失败：", $insertTags);
            return $this->err(400, "设置标签失败");
        }

        return $this->succ([]);
    }

    //修改昵称
    public function setNickname(Request $request)
    {
        $uid = $request->get('uid');

        Validator::extend('nickname', function ($attribute, $value, $parameters, $validator) {
            //UTF-8汉字字母数字下划线
            $reg = '/^[\x{4e00}-\x{9fa5}A-Za-z0-9_]+$/u';
            //获取字节长度
            $nameLength = strlen($value);
            if ($nameLength >= 1 && $nameLength <= 21 && preg_match($reg, $value)) {
                return true;
            }
            return false;
        });
        $validator = Validator::make($request->all(), [
            'nickname' => ['required', 'string', 'max:15', 'min:3', 'nickname'],
        ]);
        if ($validator->fails()) {
            $errorCode = 1070;
            $errorText = 'Data is not legal' . ':' . $validator->errors()->keys()[0] . ' ' . $validator->errors()->first();
            return $this->err($errorCode, $errorText);
        }

        $res = AccountInfo::where('uid', $uid)->update(['nickname' => $request->input('nickname')]);
        if ($res === false) {
            $errorCode = 400;
            $errorText = "Set Username Failed";
            return $this->err($errorCode, $errorText);
        }
        return $this->succ([]);
    }

    //设置出生时间
    public function setBirthday(Request $request)
    {
        $beforeDate = Carbon::parse('-18 year')->toDateString();
        $afterDate = Carbon::parse('-100 year')->toDateString();

        $uid = $request->get('uid');
        $validator = Validator::make($request->all(), [
            'birthday' => ['required', 'date_format:"Y-m-d"', 'before:' . $beforeDate, 'after:' . $afterDate],
        ]);

        if ($validator->fails()) {
            $errorCode = 1070;
            $errorText = 'Data is not legal' . ':' . $validator->errors()->keys()[0] . ' ' . $validator->errors()->first();
            return $this->err($errorCode, $errorText);
        }
        $birthday = $request->input('birthday');
        $accountInfo = AccountInfo::where("uid", $uid)->first();

        if (!$accountInfo) {
            return $this->err(404, "User Not Found");
        }
        if ($accountInfo->birthday == $birthday) {
            return $this->succ([]);
        }
        $editData = [];
        $editData['birthday'] = $birthday;
        //分析星座，年龄
        $birthday = Carbon::createFromFormat("Y-m-d", $birthday);
        $editData['age'] = $birthday->diffInYears();
        $editData['constellation'] = AccountInfo::getConstellation($birthday->month, $birthday->day);

        $res = AccountInfo::where('uid', $uid)->update(['birthday' => $request->input('birthday')]);
        if ($res === false) {
            $errorCode = 400;
            $errorText = "Set Birthday Failed";
            return $this->err($errorCode, $errorText);
        }
        return $this->succ([]);
    }

    //设置性别
    public function setSex(Request $request)
    {

        $uid = $request->get('uid');
        $validator = Validator::make($request->all(), [
            'sex' => ['required', 'integer', 'between:1,3'],
        ]);

        if ($validator->fails()) {
            $errorCode = 1070;
            $errorText = 'Data is not legal' . ':' . $validator->errors()->keys()[0] . ' ' . $validator->errors()->first();
            return $this->err($errorCode, $errorText);
        }
//        $cacheKey = "account_info:" . $uid;
//        $cacheAccountInfo = Redis::get($cacheKey);
//        $accountInfo = [];
//        if($cacheAccountInfo){
//            $accountInfo = json_decode($cacheAccountInfo, true);
//        }
//        if (!$accountInfo) {
            $accountInfo = AccountInfo::where("uid", $uid)->first();
//            Redis::set($cacheKey, json_encode($accountInfo));
//        }
        if ($accountInfo["sex"] &&  $accountInfo["sex"] != 3) {
            return $this->err(400, "性别已设置，不可修改");
        }
        $res = AccountInfo::where('uid', $uid)->update(['sex' => $request->input('sex')]);
        if ($res === false) {
            $errorCode = 400;
            $errorText = "Set Sex Failed";
            return $this->err($errorCode, $errorText);
        }
//        $accountInfo = AccountInfo::where("uid", $uid)->first();
//        Redis::set($cacheKey, json_encode($accountInfo));
        return $this->succ([]);
    }

    //选择注册目的
    public function getIntention(Request $request)
    {

        $cacheKey = "user_intention";
        $cacheData = Redis::get($cacheKey);
        $res = [];
        if($cacheData){
            $res = json_decode($cacheData, true);
        }
        if (!$res) {
            $res = DB::Table("user_intention")->where("display", 1)->get(['id', 'name'])->toArray();
            Redis::setex($cacheKey, 60, json_encode($res));
        }

        return $this->succ(["list" => $res]);
    }

    //设置注册目的
    public function setIntention(Request $request)
    {

        $uid = $request->get('uid');
        //        $validator = Validator::make($request->all(), [
        //            'intention' => [ 'required', 'array', 'min:1'],
        //            'intention.*'  => "required|integer|distinct|max:999999|min:1",
        //        ]);
        $validator = Validator::make($request->all(), [
            'intention' => ['required', 'string', 'min:1', 'max:50'],
        ]);

        if ($validator->fails()) {
            $errorCode = 1070;
            $errorText = 'Data is not legal' . ':' . $validator->errors()->keys()[0] . ' ' . $validator->errors()->first();
            return $this->err($errorCode, $errorText);
        }

        $cacheKey = "user_intention";
        $cacheData = Redis::get($cacheKey);
        $userIntention = [];
        if($cacheData){
            $userIntention = json_decode($cacheData, true);
        }
        if (!$userIntention) {
            $userIntention = DB::Table("user_intention")->where("display", 1)->get(['id', 'name'])->toArray();
            Redis::setex($cacheKey, 60, json_encode($userIntention));
        }

        $sections = array_column($userIntention, 'name', 'id');
        $inputIntention = $request->input('intention');
        $intentions = array_filter(explode(',', $inputIntention));
        foreach ($intentions as $key => $value) {
            if (!isset($sections[$value])) {
                return $this->err(400, "Error data");
                break;
            }
        }
        $cacheKey = "account_info:" . $uid;
        $cacheAccountInfo = Redis::get($cacheKey);
        $accountInfo = [];
        if($cacheAccountInfo){
            $accountInfo = json_decode($cacheAccountInfo, true);
        }
        if (!$accountInfo) {
            $accountInfo = AccountInfo::where("uid", $uid)->first();
            Redis::set($cacheKey, json_encode($accountInfo));
        }
        if ($accountInfo["intention"]) {
            return $this->err(400, "已设置，不可修改");
        }

        $res = AccountInfo::where('uid', $uid)->update(['intention' => implode(',', $intentions)]);
        if ($res === false) {
            $errorCode = 400;
            $errorText = "Set Intention Failed";
            return $this->err($errorCode, $errorText);
        }
        return $this->succ([]);
    }

    public function getAuthRadom(Request $request)
    {
        $uid = $request->get('uid');
        $validator = Validator::make($request->all(), [
            'authType' => ['required', 'string', 'in:upload_avatar,upload_video,upload_portrait,upload_dynamic'],
        ]);

        if ($validator->fails()) {
            $errorCode = 1070;
            $errorText = 'Data is not legal' . ':' . $validator->errors()->keys()[0] . ' ' . $validator->errors()->first();
            return $this->err($errorCode, $errorText);
        }
        $authType = $request->input("authType");
        $cacheKey = $authType . ":" . $uid;
        $authRadom = getRandomStr(20, false);
        Redis::setex($cacheKey, 120, $authRadom);
        return $this->succ(["random" => $authRadom]);
    }

    //上传头像
    public function uploadAvatar(Request $request)
    {

        $uid = $request->get('uid');
        $validator = Validator::make($request->all(), [
            'authRadom' => ['required', 'alpha_num', 'max:25', 'min:3'],
        ]);
        if ($validator->fails()) {
            $errorCode = 1070;
            $errorText = 'Data is not legal' . ':' . $validator->errors()->keys()[0] . ' ' . $validator->errors()->first();
            return $this->err($errorCode, $errorText);
        }
        if (!$request->hasFile("avatar") || !$request->file("avatar")->isValid()) {
            return $this->err(400, "请上传图片！");
        }
        if ($request->file("avatar")->getSize() > 4096000) {
            return $this->err(400, "上传图片不能大于4m");
        }

        $photo = $request->file("avatar");
        $extension = strtolower($photo->getClientOriginalExtension());
        $fileTypes = ["jpg", "jpeg", "bmp", "gif", "png", "webp", "apng"];
        if (!in_array($extension, $fileTypes)) {
            return $this->err(400, "请上传合法图片！");
        }
        $authRadom = $request->input("authRadom");
        $cacheKey = "upload_avatar" . ":" . $uid;
        $exs = Redis::get($cacheKey);
        if (!$exs || $authRadom != $exs) {
            return $this->err(400, "非法请求！");
        }
        Redis::del($cacheKey);
        $file_name = $photo->getClientOriginalName();
        //        echo $file_name;exit;
        //        $file = storage_path("app/images/ck.jpg");
        $s3client = new S3Client(['region' => env('AWS_DEFAULT_REGION', 'ap-southeast-1'), 'version' => 'latest']);
        $bucket_name = env('AWS_BUCKET', 'im-project');
        try {
            $s3_return = $s3client->putObject([
                'Bucket' => $bucket_name,
                'Key' => $file_name,
                'ACL' => 'public-read',
                'SourceFile' => $photo
            ]);
            Log::Info("aws图片上传返回,{$file_name}：", $s3_return->toArray());
        } catch (\Exception $e) {
            Log::error("aws图片上传失败,{$file_name}：" . $e->getMessage());
            return $this->err(500, "Image Uploaded Fail！");
        }

        if ($s3_return['@metadata']['statusCode'] == 200) {
            return $this->succ(["img_url" => $s3_return['@metadata']['effectiveUri']], "Image Uploaded Success");
        } else {
            return $this->err(400, "Image Uploaded Fail！");
        }
    }

    //申请视频上传sign
    public function applyVodSignature(Request $request)
    {

        $uid = $request->get('uid');
        $validator = Validator::make($request->all(), [
            'authRadom' => ['required', 'alpha_num', 'max:25', 'min:3'],
            'video_class' => ['required', 'alpha_num', 'in:CERTIFICATION,PERFORMANCE,DYNAMIC'],
        ]);
        if ($validator->fails()) {
            $errorCode = 1070;
            $errorText = 'Data is not legal' . ':' . $validator->errors()->keys()[0] . ' ' . $validator->errors()->first();
            return $this->err($errorCode, $errorText);
        }

        $authRadom = $request->input("authRadom");
        $videoClass = $request->input("video_class");
        $className = "VOD_" . $videoClass . "_CLASSID";
        $cacheKey = "upload_video" . ":" . $uid;
        $exs = Redis::get($cacheKey);
        if (!$exs || $authRadom != $exs) {
            return $this->err(400, "非法请求！");
        }
        Redis::del($cacheKey);
        // 确定签名的当前时间和失效时间
        $current = time();
        $expired = $current + env('VOD_UPLOADSIGN_VALID_TIME', 120);

        // 向参数列表填入参数
        $arg_list = array(
            'secretId'         => env('VOD_SECRETID'), //密钥中的 SecretId
            'classId'          => env($className, 1029068), //视频文件分类 1029068
            'currentTimeStamp' => $current, //当前 Unix 时间戳
            'taskNotifyMode'   => 'Finish', //只有当任务流全部执行完毕时，才发起一次事件通知
            'notifyMode'       => 'Finish',
            'expireTime'       => $expired, //过期时间
            'random'           => rand(), //构造签名明文串的参数，无符号 32 位随机数
            'oneTimeValid'  => 1,           //签名是否单次有效   0 表示不启用；1 表示签名单次有效
        );

        // 计算签名
        $original  = http_build_query($arg_list);
        $signature = base64_encode(hash_hmac('SHA1', $original, env('VOD_SECRETKEY'), true) . $original);

        $data["signature"] = $signature;
        return $this->succ($data);
    }

    //设置头像
    public function setAvatar(Request $request)
    {

        $uid = $request->get('uid');
        $validator = Validator::make($request->all(), [
            'avatar_url' => ['required', 'url', 'between:8,150'],
        ]);

        if ($validator->fails()) {
            $errorCode = 1070;
            $errorText = 'Data is not legal' . ':' . $validator->errors()->keys()[0] . ' ' . $validator->errors()->first();
            return $this->err($errorCode, $errorText);
        }
        $res = AccountInfo::where('uid', $uid)->update(['avatar' => $request->input("avatar_url")]);
        if ($res === false) {
            $errorCode = 400;
            $errorText = "Set Avatar Failed";
            return $this->err($errorCode, $errorText);
        }
        return $this->succ([]);
    }

    //设置社交账号
    public function setSocialAccount(Request $request)
    {
        $uid = $request->get('uid');
        $validator = Validator::make($request->all(), [
            'account_type' => ['required', 'alpha_dash', 'in:facebook,instagram,twitter,line,telegram'],
            'social_account' => ['required', 'alpha_dash', 'between:4,20'],

        ]);
        if ($validator->fails()) {
            $errorCode = 1070;
            $errorText = 'Data is not legal' . ':' . $validator->errors()->keys()[0] . ' ' . $validator->errors()->first();
            return $this->err($errorCode, $errorText);
        }
        $accountType = $request->input("account_type");
        $res = AccountInfo::where('uid', $uid)->update([$accountType => $request->input("social_account")]);
        if ($res === false) {
            $errorCode = 400;
            $errorText = "Set Social Account Failed";
            return $this->err($errorCode, $errorText);
        }
        return $this->succ([]);
    }

    //学历
    public function setEducational(Request $request)
    {
        $uid = $request->get('uid');
        //        $sections = ['High School','Some college','Associate degree','Bachelor degree','Graduate degree','PhD/Post Doctoral'];
        $params = $request->all();
        $validator = Validator::make($params, [
            'educational' => ['required', 'integer', 'between:1,6'],
        ]);

        if ($validator->fails()) {
            $errorCode = 1070;
            $errorText = 'Data is not legal' . ':' . $validator->errors()->keys()[0] . ' ' . $validator->errors()->first();
            return $this->err($errorCode, $errorText);
        }
        $isVerified = DB::table('account_certification')->where('uid', $uid)->where('status', 1)->exists();
        if ($isVerified) {
            $errorCode = 400;
            $errorText = "This account has been Verified";
            return $this->err($errorCode, $errorText);
        }
        $data = [
            'educational' => $params['educational'],
            'updated_at' => time(),
        ];
        $acm = new AccountCertification;
        $res = $acm::updateOrCreate(['uid' => $uid], $data);
        if (!$res->uid) {
            $res = $acm->find($uid);
        }
        //        $res = DB::table('account_certification')->updateOrInsert(['uid'=>$uid],$data);
        if ($res === false) {
            $errorCode = 400;
            $errorText = "Set Educational Failed";
            return $this->err($errorCode, $errorText);
        }
        return $this->succ([$res]);
    }

    //年收入
    public function setIncome(Request $request)
    {
        $uid = $request->get('uid');
        //        $sections = ['bellow $100K','$100K~$200K','$200K~$500K','$500K~$1000K','$1M~$10M','$above 10M'];
        $params = $request->all();
        $validator = Validator::make($params, [
            'income' => ['required', 'integer', 'between:1,6'],
        ]);

        if ($validator->fails()) {
            $errorCode = 1070;
            $errorText = 'Data is not legal' . ':' . $validator->errors()->keys()[0] . ' ' . $validator->errors()->first();
            return $this->err($errorCode, $errorText);
        }
        $isVerified = DB::table('account_certification')->where('uid', $uid)->where('status', 1)->exists();
        if ($isVerified) {
            $errorCode = 400;
            $errorText = "This account has been Verified";
            return $this->err($errorCode, $errorText);
        }
        $data = [
            'income' => $params['income'],
            'updated_at' => time(),
        ];
        $acm = new AccountCertification;
        $res = $acm::updateOrCreate(['uid' => $uid], $data);
        if (!$res->uid) {
            $res = $acm->find($uid);
        }
        if ($res === false) {
            $errorCode = 400;
            $errorText = "Set Income Failed";
            return $this->err($errorCode, $errorText);
        }
        return $this->succ([$res]);
    }

    //职位分类
    public function getOccupationCates(Request $request)
    {
        $cacheKey = 'occupationCates';
        $cacheData = Redis::get($cacheKey);
        $occupationCates = [];
        if ($cacheData) {
            $occupationCates = json_decode($cacheData, true);
        }
        if (!$occupationCates) {
            $occupationCates = DB::table('occupation_cate')->where('pid', 0)->get()->toArray();
            Redis::setex($cacheKey, 60, json_encode($occupationCates));
        }

        return $this->succ($occupationCates);
    }

    //工作职位分类
    public function getOccupationPosts(Request $request)
    {

        $cacheKey = 'idx_occupationCates';
        $cacheData = Redis::get($cacheKey);
        $occupationCates = [];
        if ($cacheData) {
            $occupationCates = json_decode($cacheData, true);
        }
        if (!$occupationCates) {
            $occupationCates = DB::table('occupation_cate')->where('pid', 0)->pluck('id')->toArray();
            Redis::setex($cacheKey, 60, json_encode($occupationCates));
        }
        $params = $request->all();
        $validator = Validator::make($params, [
            'occupation_cate_id' => ['required', 'integer', Rule::in($occupationCates)],
        ]);

        if ($validator->fails()) {
            $errorCode = 1070;
            $errorText = 'Data is not legal' . ':' . $validator->errors()->keys()[0] . ' ' . $validator->errors()->first();
            return $this->err($errorCode, $errorText);
        }

        $cacheKey = 'occupationPosts:' . $params['occupation_cate_id'];
        $cacheData = Redis::get($cacheKey);
        $occupationPosts = [];
        if ($cacheData) {
            $occupationPosts = json_decode($cacheData, true);
        }
        if (!$occupationPosts) {
            $occupationPosts = DB::table('occupation_cate')->where('pid', $params['occupation_cate_id'])->get()->toArray();
            Redis::setex($cacheKey, 60, json_encode($occupationPosts));
        }

        return $this->succ($occupationPosts);
    }

    //职业
    public function setOccupation(Request $request)
    {

        $uid = $request->get('uid');
        $params = $request->all();
        $validator = Validator::make($params, [
            'occupation_id' => ['required', 'integer'],
        ]);

        if ($validator->fails()) {
            $errorCode = 1070;
            $errorText = 'Data is not legal' . ':' . $validator->errors()->keys()[0] . ' ' . $validator->errors()->first();
            return $this->err($errorCode, $errorText);
        }
        $isVerified = DB::table('account_certification')->where('uid', $uid)->where('status', 1)->exists();
        if ($isVerified) {
            $errorCode = 400;
            $errorText = "This account has been Verified";
            return $this->err($errorCode, $errorText);
        }

        $row = DB::table('occupation_cate')->where('id', $params['occupation_id'])->first();
        if (!$row) {
            $errorCode = 400;
            $errorText = "Please select correct occupation";
            return $this->err($errorCode, $errorText);
        }
        $data = [
            'occupation_post' => (int)$params['occupation_id'],
            'occupation_cate' => $row->pid,
            'updated_at' => time(),
        ];

        $acm = new AccountCertification;
        $res = $acm::updateOrCreate(['uid' => $uid], $data);
        if (!$res->uid) {
            $res = $acm->find($uid);
        }
        //        $res = DB::table('account_certification')->updateOrInsert(['uid'=>$uid],$data);
        if ($res === false) {
            $errorCode = 400;
            $errorText = "Set Income Failed";
            return $this->err($errorCode, $errorText);
        }
        return $this->succ($res);
    }

    //上传肖像
    public function setPortrait(Request $request)
    {

        $uid = $request->get('uid');
        $validator = Validator::make($request->all(), [
            'authRadom' => ['required', 'alpha_num', 'max:25', 'min:3'],
            'portrait1' => 'required|image|mimes:jpeg,bmp,png,gif,jpg,webp,apng|max:4096',
            'portrait2' => 'required|image|mimes:jpeg,bmp,png,gif,jpg,webp,apng|max:4096',
            'portrait3' => 'required|image|mimes:jpeg,bmp,png,gif,jpg,webp,apng|max:4096',
        ]);
        if ($validator->fails()) {
            $errorCode = 1070;
            $errorText = 'Data is not legal' . ':' . $validator->errors()->keys()[0] . ' ' . $validator->errors()->first();
            return $this->err($errorCode, $errorText);
        }

        $authRadom = $request->input("authRadom");
        $cacheKey = "upload_portrait" . ":" . $uid;
        $exs = Redis::get($cacheKey);
        if (!$exs || $authRadom != $exs) {
            return $this->err(400, "非法请求！");
        }
        Redis::del($cacheKey);
        $s3client = new S3Client(['region' => env('AWS_DEFAULT_REGION', 'ap-southeast-1'), 'version' => 'latest']);
        $bucket_name = env('AWS_BUCKET', 'im-project');
        try {
            $result = [
                'portrait1' => '',
                'portrait2' => '',
                'portrait3' => '',
            ];
            for ($i = 1; $i < 4; $i++) {
                $portrait = 'portrait' . $i;

                $portraitObj = $request->file($portrait);
                $file_name = md5($uid . $portraitObj->getClientOriginalName() . time()) . '.' . $portraitObj->getClientOriginalExtension();
                $s3_return = $s3client->putObject([
                    'Bucket' => $bucket_name,
                    'Key' => $file_name,
                    'ACL' => 'public-read',
                    'SourceFile' => $portraitObj
                ]);
                Log::Info("aws图片上传返回,{$file_name}：", $s3_return->toArray());
                if ($s3_return['@metadata']['statusCode'] == 200) {
                    $result[$portrait] = $s3_return['@metadata']['effectiveUri'];
                }
            }
            $data = array_merge(['updated_at' => time()], $result);
            $acm = new AccountCertification;
            $res = $acm::updateOrCreate(['uid' => $uid], $data);
            if (!$res->uid) {
                $res = $acm->find($uid);
            }
            if ($res === false) {
                $errorCode = 400;
                $errorText = "Set Portrait Failed";
                return $this->err($errorCode, $errorText);
            }
            return $this->succ($res);
        } catch (\Exception $e) {
            Log::error("aws图片上传失败,：" . $e->getMessage());
            return $this->err(400, "Image Uploaded Fail！");
        }
    }

    public function setVerifyVideo(Request $request)
    {
        $uid = $request->get('uid');
        $params = $request->all();
        $validator = Validator::make($params, [
            'verify_video' => ['required', 'url', 'max:450'],
        ]);

        if ($validator->fails()) {
            $errorCode = 1070;
            $errorText = 'Data is not legal' . ':' . $validator->errors()->keys()[0] . ' ' . $validator->errors()->first();
            return $this->err($errorCode, $errorText);
        }
        $data = [
            'verify_video' => $params['verify_video'],
            'updated_at' => time(),
        ];
        $acm = new AccountCertification;
        $res = $acm::updateOrCreate(['uid' => $uid], $data);
        if (!$res->uid) {
            $res = $acm->find($uid);
        }
        if ($res === false) {
            $errorCode = 400;
            $errorText = "Set Verify Video Failed";
            return $this->err($errorCode, $errorText);
        }
        return $this->succ($res);
    }

    //请求视频认证
    public function setPerfVideo(Request $request)
    {

        $uid = $request->get('uid');
        $validator = Validator::make($request->all(), [
            'video_url' => ['required', 'url', 'between:15,150'],
        ]);

        if ($validator->fails()) {
            $errorCode = 1070;
            $errorText = 'Data is not legal' . ':' . $validator->errors()->keys()[0] . ' ' . $validator->errors()->first();
            return $this->err($errorCode, $errorText);
        }

        $res = AccountInfo::where('uid', $uid)->update(['verify_video' => $request->input("video_url"), 'verified' => 2]);
        if ($res === false) {
            $errorCode = 400;
            $errorText = "Set Avatar Failed";
            return $this->err($errorCode, $errorText);
        }
        return $this->succ([]);
    }

    //编辑资料
    public function editInfo(Request $request)
    {
        $uid = $request->get('uid');
        $params = $request->all();
        $beforeDate = Carbon::parse('-18 year')->toDateString();
        $afterDate = Carbon::parse('-100 year')->toDateString();
        Validator::extend('nickname', function ($attribute, $value, $parameters, $validator) {
            //UTF-8汉字字母数字下划线
            $reg = '/^[\x{4e00}-\x{9fa5}A-Za-z0-9_]+$/u';
            //获取字节长度
            $nameLength = strlen($value);
            if ($nameLength >= 1 && $nameLength <= 21 && preg_match($reg, $value)) {
                return true;
            }
            return false;
        });
        $validator = Validator::make($request->all(), [
            'avatar_url' => ['required', 'active_url', 'between:8,150'],
            'nickname' => ['required', 'string', 'max:15', 'min:3', 'nickname'],
            'sex' => ['required', 'integer', 'between:1,3'],
            'account_type' => ['required', 'alpha_dash', 'in:facebook,instagram,twitter,line,telegram'],
            'social_account' => ['required', 'alpha_dash', 'between:4,20'],
            'birthday' => ['required', 'date_format:"Y-m-d"', 'before:' . $beforeDate, 'after:' . $afterDate],
            'bio' => ['required', 'alpha_dash', 'max:50'],
            'height' => ['required', 'integer', 'between:100,260'],
            'weight' => ['required', 'integer', 'between:30,500'],
        ]);
        if ($validator->fails()) {
            $errorCode = 1070;
            $errorText = 'Data is not legal' . ':' . $validator->errors()->keys()[0] . ' ' . $validator->errors()->first();
            return $this->err($errorCode, $errorText);
        }
        $accountInfo = AccountInfo::where("uid", $uid)->first();
        if (!$accountInfo) {
            return $this->err(404, "User Not Found");
        }

        $editData = [
            "avatar" => $params['avatar_url'],
            "nickname" => $params['nickname'],
            "sex" => $params['sex'],
            $params['account_type'] => $params['social_account'],
            "birthday" => $params['birthday'],
            "bio" => $params['bio'],
            "height" => $params['height'],
            "weight" => $params['weight'],
        ];
        if ($accountInfo->birthday != $params['birthday']) {
            //重新计算年龄，星座
            $birthday = Carbon::createFromFormat("Y-m-d", $params['birthday']);
            $editData['age'] = $birthday->diffInYears();
            $editData['constellation'] = AccountInfo::getConstellation($birthday->month, $birthday->day);
        }
        $res = AccountInfo::where('uid', $uid)->update($editData);

        if ($res === false) {
            $errorCode = 400;
            $errorText = "Set Account Failed";
            return $this->err($errorCode, $errorText);
        }
        return $this->succ([]);
    }

    //账号资料
    public function getInfo(Request $request)
    {
        try {
            $uid = $request->get('uid');
            $account = Account::where('uid', $uid)->first();
            $accountInfo = AccountInfo::leftjoin('account','account_info.uid', '=', 'account.uid')->where("account_info.uid", $uid)->first(["account_info.*","account.vip_level","account.gold","account.voucher"]);
            if (!$accountInfo) {
                return $this->err(404, "User Not Found");
            }
            $mobileInfo = DB::table("account_mobile")->where('uid',$uid)->first();
            if($mobileInfo){
                $accountInfo->account_type = 1;
                $accountInfo->mobile_area = $mobileInfo->mobile_area;
                $accountInfo->mobile_number = $mobileInfo->mobile_number;
            }else{
                $accountInfo->account_type = 0;
                $accountInfo->mobile_area = "";
                $accountInfo->mobile_number = "";
            }
            $tags = DB::table("account_tag as a")->leftJoin('user_tag as b', 'a.tag_id', '=', 'b.tag_id')->where('a.uid', $uid)->get(["a.tag_id", "a.tag_name", "b.attr_id"])->toArray();
            $accountInfo->tags = $tags;
            $accountInfo->is_password = !empty($account->password) ? 1 : 0;
            $acm = new AccountCertification;
            $accountCertification = $acm->firstOrCreate(['uid' => $uid], ['uid' => $uid, 'created_at' => time()]);
            if (!$accountCertification->uid) {
                $accountCertification = $acm->find($uid);
            }
            $accountInfo->accountCertification = $accountCertification;

            $cacheKey = 'ever_seen_countries:' . $uid;
            $everSeenCountries = Redis::SMembers($cacheKey);
            $accountInfo->ever_seen_countries = $everSeenCountries;
            return $this->succ($accountInfo);
        } catch (\Exception $e) {
            Log::error('getInfo error :' . $e->getMessage());
            return $this->succ([]);
        }
    }

    //查看他人账号资料
    public function getInfoByUid(Request $request, $uuid)
    {
        try {
            $uid = $request->get('uid');
            $accountInfo = AccountInfo::where("uid", $uuid)->first();
            if (!$accountInfo) {
                return $this->err(404, "User Not Found");
            }
            DB::table("visitor_log")->where('uid',$uid)->where('visit_uid',$uuid)->updateOrInsert(["uid" => $uid,
                "visit_uid" => $uuid],["updated_at" => time(),"created_at"=>time()]);
            $tags = DB::table("account_tag as a")->leftJoin('user_tag as b', 'a.tag_id', '=', 'b.tag_id')->where('a.uid', $uuid)->get(["a.tag_id", "a.tag_name", "b.attr_id"])->toArray();
            $accountInfo->tags = $tags;

            $acm = new AccountCertification;
            $accountCertification = $acm->firstOrCreate(['uid' => $uuid], ['uid' => $uuid, 'created_at' => time()]);
            if (!$accountCertification->uid) {
                $accountCertification = $acm->find($uuid);
            }
            $accountInfo->accountCertification = $accountCertification;

            $cacheKey = 'ever_seen_countries:' . $uuid;
            $everSeenCountries = Redis::SMembers($cacheKey);
            $accountInfo->ever_seen_countries = $everSeenCountries;
            $self = AccountInfo::where("uid", $uid)->first();
            if(!$self->im_userid || !$accountInfo->im_userid){
                $accountInfo->is_friend = 0;
            }else{
                $accountInfo->is_friend = AccountInfo::friendCheckSingle($self->im_userid, $accountInfo->im_userid);
            }
            return $this->succ($accountInfo);
        } catch (\Exception $e) {
            Log::error('getInfo error :' . $e->getMessage());
            return $this->succ([]);
        }
    }

    //通过imid查看他人账号资料
    public function getInfoByImid(Request $request, $imid)
    {
        try {
            $uid = $request->get('uid');
            $accountInfo = AccountInfo::where("im_userid", $imid)->first();
            if (!$accountInfo) {
                return $this->err(404, "User Not Found");
            }
            $uuid = $accountInfo->uid;

            DB::table("visitor_log")->where('uid',$uid)->where('visit_uid',$uuid)->updateOrInsert(["uid" => $uid,
                "visit_uid" => $uuid],["updated_at" => time(),"created_at"=>time()]);
            $tags = DB::table("account_tag as a")->leftJoin('user_tag as b', 'a.tag_id', '=', 'b.tag_id')->where('a.uid', $uuid)->get(["a.tag_id", "a.tag_name", "b.attr_id"])->toArray();
            $accountInfo->tags = $tags;

            $acm = new AccountCertification;
            $accountCertification = $acm->firstOrCreate(['uid' => $uuid], ['uid' => $uuid, 'created_at' => time()]);
            if (!$accountCertification->uid) {
                $accountCertification = $acm->find($uuid);
            }
            $accountInfo->accountCertification = $accountCertification;

            $cacheKey = 'ever_seen_countries:' . $uuid;
            $everSeenCountries = Redis::SMembers($cacheKey);
            $accountInfo->ever_seen_countries = $everSeenCountries;

            $self = AccountInfo::where("uid", $uid)->first();
            if(!$self->im_userid || !$accountInfo->im_userid){
                $accountInfo->is_friend = 0;
            }else{
                $accountInfo->is_friend = AccountInfo::friendCheckSingle($self->im_userid, $accountInfo->im_userid);
            }

            return $this->succ($accountInfo);
        } catch (\Exception $e) {
            Log::error('getInfo error :' . $e->getMessage());
            return $this->succ([]);
        }
    }

    //访客列表
    public function getVisitorList(Request $request){
        $uid = $request->get('uid');
        $page = isset($params['page']) && !empty($params['page']) && is_numeric($params['page']) ? $params['page'] : 1;
        $page_size = isset($params['page_size']) && !empty($params['page_size']) && is_numeric($params['page_size']) ? $params['page'] : 10;
        $query = DB::table("visitor_log as a")->leftJoin("account_info as b",'a.uid','=','b.uid')->where('a.visit_uid',$uid);

        $visitList = $query->orderByDesc("visited_time")->get(["a.visited_time","b.uid","b.nickname","b.birthday","b.city","b.constellation"])->forPage($page,$page_size);

        $attributes['total'] = $query->count();
        $attributes['page'] = $page;
        $attributes['page_size'] = $page_size;

        foreach ($visitList as $value){
            $tags = DB::table("account_tag as a")->leftJoin('user_tag as b', 'a.tag_id', '=', 'b.tag_id')->where('a.uid', $uid)->get(["a.tag_id", "a.tag_name", "b.attr_id"])->toArray();
            $value->tags = $tags;
        }
        $data['list'] = $visitList;
        $data['attributes'] = $attributes;
        return $this->succ($data);

    }

    //系统消息列表
    public function getSystemNotifyList(Request $request){
        $uid = $request->get('uid');
        $page = isset($params['page']) && !empty($params['page']) && is_numeric($params['page']) ? $params['page'] : 1;
        $page_size = isset($params['page_size']) && !empty($params['page_size']) && is_numeric($params['page_size']) ? $params['page'] : 10;
        $newCount = DB::table("user_system_notices")->where("uid",$uid)->where("is_read",0)->count("id");

        $query = DB::table("user_system_notices")->where("uid",$uid);

        $list = $query->selectRaw("id,type,content,created_at")->orderByDesc("id")->orderBy("is_read")->forPage($page,$page_size)->get()->toArray();

        $data['newCount'] = $newCount;
        $data['list'] = $list;
        $attributes['total'] = $query->count();
        $attributes['page'] = $page;
        $attributes['page_size'] = $page_size;
        $data['attributes'] = $attributes;
        return $this->succ($data);

    }

    //批量阅读系统消息
    public function batchReadSystemNotices(Request $request){
        $uid = $request->get('uid');
        //        $validator = Validator::make($request->all(), [
        //            'intention' => [ 'required', 'array', 'min:1'],
        //            'intention.*'  => "required|integer|distinct|max:999999|min:1",
        //        ]);
        $validator = Validator::make($request->all(), [
            'notices' => ['required', 'string', 'min:1', 'max:250'],
        ]);

        if ($validator->fails()) {
            $errorCode = 1070;
            $errorText = 'Data is not legal' . ':' . $validator->errors()->keys()[0] . ' ' . $validator->errors()->first();
            return $this->err($errorCode, $errorText);
        }
        $notices = $request->input('notices');
        $noticeArr = array_filter(explode(',', $notices));
        foreach ($noticeArr as $value){
            DB::table("user_system_notices")->where("uid",$uid)->where("id",$value)->update(["is_read"=>1]);
        }
        return $this->succ([]);
    }

    //批量删除系统消息
    public function batchDelSystemNotices(Request $request){
        $uid = $request->get('uid');
        //        $validator = Validator::make($request->all(), [
        //            'intention' => [ 'required', 'array', 'min:1'],
        //            'intention.*'  => "required|integer|distinct|max:999999|min:1",
        //        ]);
        $validator = Validator::make($request->all(), [
            'notices' => ['required', 'string', 'min:1', 'max:250'],
        ]);

        if ($validator->fails()) {
            $errorCode = 1070;
            $errorText = 'Data is not legal' . ':' . $validator->errors()->keys()[0] . ' ' . $validator->errors()->first();
            return $this->err($errorCode, $errorText);
        }
        $notices = $request->input('notices');
        $noticeArr = array_filter(explode(',', $notices));
        if(!count($noticeArr)){
            return $this->succ([]);
        }
        DB::table("user_system_notices")->where("uid",$uid)->whereIn("id",$noticeArr)->delete();

        return $this->succ([]);
    }
}
