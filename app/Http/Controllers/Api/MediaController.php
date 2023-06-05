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
class MediaController extends ApiController
{
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

    public function uploadImg(Request $request){

        if(!$request->hasFile("photo") || !$request->file("photo")->isValid()){
            return $this->err(400,"请上传图片！");
        }
        if($request->file("photo")->getSize() > 4096000){
            return $this->err(400,"上传图片不能大于4m");
        }
        $photo = $request->file("photo");
        $extension = strtolower($photo->extension());
        $fileTypes = ["jpg","jpeg","bmp","gif","png","webp","apng"];
        if(!in_array($extension,$fileTypes)){
            return $this->err(400,"请上传合法图片！");
        }

        $file_name = $photo->getClientOriginalName();
//        echo $file_name;exit;
//        $file = storage_path("app/images/ck.jpg");
        $s3client = new S3Client(['region' => env('AWS_DEFAULT_REGION', 'ap-southeast-1'), 'version' => 'latest']);
        $bucket_name = env('AWS_BUCKET', 'im-project');
        try {
            $s3_return = $s3client->putObject([
                'Bucket' => $bucket_name,
                'Key' => $file_name,
                'SourceFile' => $photo
            ]);
            Log::Info("aws图片上传返回,{$file_name}：", $s3_return);
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

    public function setCoordinate(Request $request){

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
        $validator = Validator::make($params, [
            'lat' => [ 'required', 'string', 'latlong'],//纬度
            'long' => [ 'required', 'string', 'latlong'],//经度
            'client_ip' => [  'ip', 'between:5,20'],//ip
            'country' => [ 'required', 'string', 'between:2,25'],//国家
            'province' => [ 'required', 'string', 'between:2,25'],//省份
            'city' => [ 'required', 'string', 'between:2,25'],//城市
        ]);

        if ($validator->fails()) {
            $errorCode = 1070;
            $errorText = 'Data is not legal' . ':' . $validator->errors()->keys()[0] . ' ' . $validator->errors()->first();
            return $this->err($errorCode, $errorText);
        }
        if(!isset($params['client_ip'])||empty($params['client_ip'])){
            $params['client_ip'] = $request->getClientIp();
        }
        $editData = [
            'lat' => $params['lat'],//纬度
            'long' => $params['lat'],//经度
            'client_ip' => $params['client_ip'],//ip
            'country' => $params['country'],//国家
            'province' => $params['province'],//省份
            'city' => $params['city'],//城市
        ];
        try{
            $res = AccountInfo::where('uid', $uid)->update($editData);
        }catch (\Exception $e){
            Log::Error("$uid 设置坐标失败：".$e->getMessage());
            return $this->err(501, "System Error");
        }

        if ($res === false) {
            $errorCode = 400;
            $errorText = "Set Account Failed";
            return $this->err($errorCode, $errorText);
        }
        return $this->succ([]);

    }

}