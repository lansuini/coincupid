<?php
/**
 * Created by PhpStorm.
 * User: luobinhan
 * Date: 2023/2/19
 * Time: 3:43
 */

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Aws\S3\S3Client;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\App;
use Aws\Laravel\AwsFacade as AWS;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\DB;
class UserDynamicController extends ApiController
{

    public function uploadImage(Request $request){
        $uid = $request->get('uid');
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,bmp,png,gif,jpg,webp,apng|max:4096',
        ]);
        if ($validator->fails()) {
            $errorCode = 1070;
            $errorText = 'Data is not legal' . ':' . $validator->errors()->keys()[0] . ' ' . $validator->errors()->first();
            return $this->err($errorCode, $errorText);
        }

        $photo = $request->file("image");
        $extension = strtolower($photo->getClientOriginalExtension());
        $fileTypes = ["jpg","jpeg","bmp","gif","png","webp","apng"];
        if(!in_array($extension,$fileTypes)){
            return $this->err(400,"请上传合法图片！");
        }
//        $authRadom = $request->input("authRadom");
//        $cacheKey = "upload_avatar" . ":".$uid ;
//        $exs = Redis::get($cacheKey);
//        if (!$exs || $authRadom != $exs){
//            return $this->err(400,"非法请求！");
//        }
//        Redis::del($cacheKey);
        $file_name = md5($uid . $photo->getClientOriginalName() .time()) . '.'. $photo->getClientOriginalExtension();
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
            return $this->err(500,"Image Uploaded Fail！");
        }

        if($s3_return['@metadata']['statusCode'] == 200){
            return $this->succ(["img_url"=> $s3_return['@metadata']['effectiveUri']],"Image Uploaded Success");
        } else {
            return $this->err(400,"Image Uploaded Fail！");
        }
    }

    public function publishDynamic(Request $request){
        $uid = $request->get('uid');
        $params = $request->all();
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:1,2',
            'auth' => 'required|in:1,2,3',
            'images' => 'required_if:type,1|array',
            'images.*'  => 'required_if:type,1|url',
            'video' => 'required_if:type,2|url',
            'content' => 'required|max:150|min:1',
            'location' => 'max:150|min:1',
        ]);
        if ($validator->fails()) {
            $errorCode = 1070;
            $errorText = 'Data is not legal' . ':' . $validator->errors()->keys()[0] . ' ' . $validator->errors()->first();
            return $this->err($errorCode, $errorText);
        }
        $dynamic = [
            'uid'=>$uid,
            'type'=>$params['type'],
            'auth'=>$params['auth'],
            'content'=>$params['content'],
            'location'=>$params['location'],
            'created_at'=>time(),
        ];
        try{
            DB::beginTransaction();
            $dynamicId = DB::table('user_dynamics')->insertGetId($dynamic);
            if($params['type'] == 1){
                $rows = [];
                foreach ($params['images'] as $v){
                    $row = [
                        'uid' => $uid,
                        'dynamic_id' => $dynamicId,
                        'picture' => $v,
                        'created_at'=>time(),
                    ];
                    array_push($rows,$row);
                }

                DB::table('user_dynamic_pictures')->insert($rows);
            }elseif ($params['type'] == 2){

                DB::table('user_dynamics')->where('id',$dynamicId)->update(['video'=>$params['video']]);
            }
            DB::commit();

        }catch (\Exception $e){
            DB::rollBack();
            Log::error('发布动态失败-' . $e->getMessage());
            return $this->err(400,'发布动态失败');
        }

        return $this->succ(['dynamic_id'=>$dynamicId]);
    }

    //修改动态是否对外显示
    public function setDynamicAuth(Request $request,$dynamicId){
        $uid = $request->get('uid');
        $params = $request->all();
        $validator = Validator::make($request->all(), [
            'auth' => 'required|in:1,2,3',
        ]);
        if ($validator->fails()) {
            $errorCode = 1070;
            $errorText = 'Data is not legal' . ':' . $validator->errors()->keys()[0] . ' ' . $validator->errors()->first();
            return $this->err($errorCode, $errorText);
        }
        $dynamic = DB::table('user_dynamics')->find($dynamicId);
        if(!$dynamic){
            return $this->err(400,'动态不存在！');
        }
        if($dynamic->uid != $uid){
            return $this->err(400,'您无权限修改！');
        }
        try{
            $res = DB::table('user_dynamics')->where('id',$dynamicId)->update(['auth'=>$params['auth']]);
            if($res === false){
                return $this->err(400,'修改动态显示失败');
            }

        }catch (\Exception $e){
            Log::error('修改动态显示失败-' . $e->getMessage());
            return $this->err(400,'修改动态显示失败');
        }

        return $this->succ([]);
    }

    //删除动态
    public function delDynamic(Request $request,$dynamicId){
        $uid = $request->get('uid');
        $dynamic = DB::table('user_dynamics')->find($dynamicId);
        if(!$dynamic){
            return $this->err(400,'动态不存在！');
        }
        if($dynamic->uid != $uid){
            return $this->err(400,'您无权限修改！');
        }
        try{
            $res = DB::table('user_dynamics')->where('id',$dynamicId)->update(['deleted_at'=>time()]);
            if($res === false){
                return $this->err(400,'删除动态失败');
            }

        }catch (\Exception $e){
            Log::error('删除动态失败-' . $e->getMessage());
            return $this->err(400,'删除动态失败');
        }

        return $this->succ([]);
    }

    public function getDynamics(Request $request,$uuid){

        $uid = $request->get('uid');
        $params = $request->all();
        $page = isset($params['page']) && !empty($params['page']) && is_numeric($params['page']) ? $params['page'] : 1;
        $page_size = isset($params['page_size']) && !empty($params['page_size']) && is_numeric($params['page_size']) ? $params['page'] : 10;
        if($uid != $uuid){

            $query = DB::table('user_dynamics')->where('uid',$uuid)->whereRaw('deleted_at is null')->where('auth',3);

        }else{
            $query = DB::table('user_dynamics')->whereRaw('deleted_at is null')->where('uid',$uid);

        }
        $dynamics = $query->forPage($page,$page_size)->orderByDesc('created_at')->get()->toArray();

        foreach ($dynamics as $dynamic){
            $dynamic->pictures = [];
            if($dynamic->type == 1){
                $pics = DB::table('user_dynamic_pictures')->where('dynamic_id',$dynamic->id)->pluck('picture');
                $dynamic->pictures = $pics ?? [];
            }
            $commentList = DB::table('user_dynamic_comments as a')->leftJoin('account_info as b','a.uid','=','b.uid')->where('a.dynamic_id',$dynamic->id)->where("a.pid",0)->limit(10)->get(["a.id","a.uid","a.dynamic_id","a.content","b.nickname","b.avatar","a.created_at",])->toArray();
            $dynamic->comment_list = $commentList;
            $islike = DB::table('user_dynamic_likes')->where('dynamic_id',$dynamic->id)->where('uid',$uid)->exists();
            $dynamic->is_like = $islike;
        }
        $data['list'] = $dynamics;
        $userInfo = DB::table('account_info as a')->leftJoin('account as b','a.uid','=','b.uid')->selectRaw("a.nickname,a.avatar,a.verified,b.vip_level,a.popularity,a.birthday,a.constellation,a.city")->where('a.uid',$uid)->first();
        $data['user_info'] = $userInfo;

        $attributes['total'] = $query->count();
        $attributes['page'] = $page;
        $attributes['page_size'] = $page_size;
        $data['attributes'] = $attributes;
        return $this->succ($data);
    }

    //获取动态评论
    public function getCommentsById(Request $request,$dynamicId){

        $uid = $request->get('uid');
        $params = $request->all();
        $page = isset($params['page']) && !empty($params['page']) && is_numeric($params['page']) ? $params['page'] : 1;
        $page_size = isset($params['page_size']) && !empty($params['page_size']) && is_numeric($params['page_size']) ? $params['page'] : 10;

        $query = DB::table('user_dynamic_comments as a')->leftJoin('account_info as b','a.uid','=','b.uid')->where('dynamic_id',$dynamicId)->where('pid',0);

        $comments =$query->forPage($page,$page_size)->selectRaw("a.*,b.nickname,b.avatar")->get()->toArray();

        $data['list'] = $comments;

        $attributes['total'] = $query->count();
        $attributes['page'] = $page;
        $attributes['page_size'] = $page_size;
        $data['attributes'] = $attributes;
        return $this->succ($data);
    }

    public function getRepliesById(Request $request,$commentId){

        $uid = $request->get('uid');
        $comments = DB::table('user_dynamic_comments as a')->leftJoin('account_info as b','a.uid','=','b.uid')->where('a.pid',$commentId)->selectRaw("a.*,b.nickname,b.avatar")->get()->toArray();

        return $this->succ($comments);
    }

    public function getDynamicById(Request $request,$dynamicId){

        $uid = $request->get('uid');
        $dynamic = DB::table('user_dynamics')->find($dynamicId);
        if(!$dynamic){
            $errorCode = 404;
            $errorText = 'dynamic is not exists';
            return $this->err($errorCode, $errorText);
        }
        if($dynamic->auth == 1 && $uid != $dynamic->uid){
            $errorCode = 404;
            $errorText = 'You do not have permission to view';
            return $this->err($errorCode, $errorText);
        }
        if($dynamic->type == 1){
            $pics = DB::table('user_dynamic_pictures')->where('dynamic_id',$dynamic->id)->pluck('picture');
            $dynamic->pictures = $pics ?? [];
        }

        $islike = DB::table('user_dynamic_likes')->where('dynamic_id',$dynamic->id)->where('uid',$uid)->exists();
        $dynamic->is_like = $islike;
        $commentList = DB::table('user_dynamic_comments as a')->leftJoin('account_info as b','a.uid','=','b.uid')->where('a.dynamic_id',$dynamic->id)->where("a.pid",0)->limit(10)->get(["a.id","a.uid","a.dynamic_id","a.content","b.nickname","b.avatar","a.created_at",])->toArray();
        $likeList = DB::table('user_dynamic_likes as a')->leftJoin('account_info as b','a.uid','=','b.uid')->where('a.dynamic_id',$dynamic->id)->limit(10)->get(["a.uid","b.nickname","b.avatar",])->toArray();
        $dynamic->comment_list = $commentList;
        $dynamic->like_list = $likeList;
        $userInfo = DB::table('account_info as a')->leftJoin('account as b','a.uid','=','b.uid')->selectRaw("a.nickname,a.avatar,a.verified,b.vip_level,a.popularity,a.birthday,a.constellation,a.city")->where('a.uid',$uid)->first();
        $dynamic->user_info = $userInfo;
        return $this->succ($dynamic);
    }

    public function postComment(Request $request,$dynamicId){

        $uid = $request->get('uid');
        $params = $request->all();
        $validator = Validator::make($request->all(), [
            'reply_comment_id' => 'required|integer',
            'content' => 'required|max:150|min:1',
        ]);
        if ($validator->fails()) {
            $errorCode = 1070;
            $errorText = 'Data is not legal' . ':' . $validator->errors()->keys()[0] . ' ' . $validator->errors()->first();
            return $this->err($errorCode, $errorText);
        }
        $dynamic = DB::table('user_dynamics')->find($dynamicId);
        if(!$dynamic){
            $errorCode = 400;
            $errorText = 'Data is not exists';
            return $this->err($errorCode, $errorText);
        }
        $replyComment = '';
        if($params['reply_comment_id']){
            $replyComment = DB::table('user_dynamic_comments')->find($params['reply_comment_id']);
            if(!$replyComment || $replyComment->dynamic_id != $dynamicId){
                $errorCode = 400;
                $errorText = 'Comment is not exists';
                return $this->err($errorCode, $errorText);
            }
        }

        try{
            DB::beginTransaction();
            $row = [
                'uid'=>$uid,
                'pid'=>0,
                'dynamic_id'=>$dynamicId,
                'content'=>$params['content'],
                'level'=>1,
                'created_at'=>time(),
            ];
            if($params['reply_comment_id']){
                $row['level'] = $replyComment->level + 1;
                $row['pid'] = $replyComment->id;
                DB::table('user_dynamic_comments')->where('id',$params['reply_comment_id'])->increment('replies',1);
            }
            $commentId = DB::table('user_dynamic_comments')->insertGetId($row);
            $cover = "";
            if($dynamic->type == 1){
                $rowDyPic = DB::table("user_dynamic_pictures")->where('dynamic_id',$dynamicId)->first();
                if($rowDyPic){
                    $cover = $rowDyPic->picture;
                }
            }else{
                $cover = $dynamic->video;
            }
            $notice = [
                'dynamic_id' => $dynamic->id,
                'uid' => $dynamic->uid,
                'friend' => $uid,
                'type' => 2,
                'comment_id' => $commentId,
                'content' => $params['content'],
                'cover' => $cover,
            ];
            DB::table('user_dynamic_notices')->insert($notice);
            DB::table('user_dynamics')->where('id',$dynamicId)->increment('comments',1);
            if(!$params['reply_comment_id']){
                DB::table('user_dynamics')->where('id',$dynamicId)->increment('comments_l1',1);
            }
            DB::commit();
        }catch (\Exception $e){
            DB::rollBack();
            Log::error('postComment failed : '.$e->getMessage());
            $errorCode = 400;
            $errorText = 'Failed to reply this post';
            return $this->err($errorCode, $errorText);
        }

        return $this->succ([]);
    }

    public function postLike(Request $request,$dynamicId){
        $uid = $request->get('uid');
        $dynamic = DB::table('user_dynamics')->find($dynamicId);
        if(!$dynamic){
            $errorCode = 400;
            $errorText = 'Data is not exists';
            return $this->err($errorCode, $errorText);
        }

        try{
            DB::beginTransaction();
            $row = [
                'uid'=>$uid,
                'dynamic_id'=>$dynamicId,
                'created_at'=>time(),
            ];
            DB::table('user_dynamic_likes')->insert($row);
            $cover = "";
            if($dynamic->type == 1){
                $rowDyPic = DB::table("user_dynamic_pictures")->where('dynamic_id',$dynamicId)->first();
                if($rowDyPic){
                    $cover = $rowDyPic->picture;
                }
            }else{
                $cover = $dynamic->video;
            }
            $notice = [
                'dynamic_id' => $dynamic->id,
                'uid' => $dynamic->uid,
                'friend' => $uid,
                'cover' => $cover,
                'type' => 1,
            ];
            DB::table('user_dynamic_notices')->insert($notice);
            DB::table('user_dynamics')->where('id',$dynamicId)->increment('likes',1);

            DB::commit();
        }catch (\Exception $e){
            DB::rollBack();
            Log::error('postLike failed : '.$e->getMessage());
            $errorCode = 400;
            $errorText = 'Failed to like this post';
            return $this->err($errorCode, $errorText);
        }

        return $this->succ([]);
    }

    public function notices(Request $request){
        $uid = $request->get('uid');
        $page = isset($params['page']) && !empty($params['page']) && is_numeric($params['page']) ? $params['page'] : 1;
        $page_size = isset($params['page_size']) && !empty($params['page_size']) && is_numeric($params['page_size']) ? $params['page'] : 10;
        $newCount = DB::table("user_dynamic_notices")->where("uid",$uid)->where("is_read",0)->count("id");

        $query = DB::table("user_dynamic_notices as a")->where("a.uid",$uid);

        $list = $query->leftJoin('account_info as b','a.friend','=','b.uid')->selectRaw("a.id,a.type,a.comment_id,a.dynamic_id,a.uid,a.friend,b.nickname,b.avatar,a.content,a.cover,a.is_read,a.created_at")->orderBy("is_read")->orderByDesc("id")->forPage($page,$page_size)->get()->toArray();

        $data['newCount'] = $newCount;
        $data['list'] = $list;
        $attributes['total'] = $query->count();
        $attributes['page'] = $page;
        $attributes['page_size'] = $page_size;
        $data['attributes'] = $attributes;
        return $this->succ($data);
    }

    //批量阅读消息
    public function batchReadNotices(Request $request){
        $uid = $request->get('uid');
        //        $validator = Validator::make($request->all(), [
        //            'intention' => [ 'required', 'array', 'min:1'],
        //            'intention.*'  => "required|integer|distinct|max:999999|min:1",
        //        ]);
//        $validator = Validator::make($request->all(), [
//            'notices' => ['required', 'string', 'min:1', 'max:250'],
//        ]);
//
//        if ($validator->fails()) {
//            $errorCode = 1070;
//            $errorText = 'Data is not legal' . ':' . $validator->errors()->keys()[0] . ' ' . $validator->errors()->first();
//            return $this->err($errorCode, $errorText);
//        }
//        $notices = $request->input('notices');
//        $noticeArr = array_filter(explode(',', $notices));
//        foreach ($noticeArr as $value){
//            DB::table("user_dynamic_notices")->where("uid",$uid)->where("id",$value)->update(["is_read"=>1]);
//        }
        DB::table("user_dynamic_notices")->where("uid",$uid)->where("is_read",0)->update(["is_read"=>1]);
        return $this->succ([]);
    }

    //批量阅读消息
    public function readNotice(Request $request,$notice_id){

        $uid = $request->get('uid');

        DB::table("user_dynamic_notices")->where("uid",$uid)->where("id",$notice_id)->update(["is_read"=>1]);

        return $this->succ([]);
    }

    public function applyVodSignature(Request $request){

        $vodKeys = [
            "Certification" => 1029068
        ];
        $rsp = [
            "errorCode"=>400,
            "message"=>"",
            "data"=> ["signature"=>""],
            "success"=>false,
        ];
//        $class = $request->query->get('class',"Certification");
//        if (empty($class) || !in_array($class,$vodKeys)){
//            $rsp["message"] = "class not exists";
//            return response()->json($rsp);
//        }

        // 确定签名的当前时间和失效时间
        $current = time();
        $expired = $current + env('VOD_UPLOADSIGN_VALID_TIME',60);

        // 向参数列表填入参数
        $arg_list = array(
            'secretId'         => env('VOD_SECRETID'), //密钥中的 SecretId
            'classId'          => 1029068, //视频文件分类 1029068
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
//        $rsp["errorCode"] = 200;
//        $rsp["success"] = true;
//        return response()->json($rsp);
    }

}