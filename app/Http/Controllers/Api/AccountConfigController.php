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
use App\Models\AccountInfo;
use App\Models\AccountConfig;
class AccountConfigController extends ApiController
{
    //会员等级
    public function testAccountConfig(){
        AccountInfo::chunk(100,function($users){
            foreach($users as $user){
                try{
                    $data = [
                        'uid'=>$user->uid,
                    ];
                    DB::table("account_config")->insert($data);

                }catch (\Exception $e){
                    continue;
                }

            }
        });
    }

    //获取账号设置
    public function getAccountConfig(Request $request){
        $uid = $request->get('uid');
        $cacheKey = 'account_config:' . $uid;
        $accountConfigJson = Redis::get($cacheKey);
        if(!$accountConfigJson){
            $accountConfig = AccountConfig::find($uid);
            Redis::setex($cacheKey,60,json_encode($accountConfig));
        }else{
            $accountConfig = json_decode($accountConfigJson,true);
        }

        return $this->succ(['accountConfig'=>$accountConfig]);
    }

    //隐私设置
    public function privacySet(Request $request){
        $uid = $request->get('uid');
        $validator = Validator::make($request->all(), [
            'key' => ['required', 'string', 'in:hideTrace,hideNewVisitorRemind,hideNoticeContent,hideOnlineStatus,hideLocation,hideRankingList'],
            'status' => ['required', 'integer', 'in:0,1'],
        ]);

        if ($validator->fails()) {
            $errorCode = 1070;
            $errorText = 'Data is not legal' . ':' . $validator->errors()->keys()[0] . ' ' . $validator->errors()->first();
            return $this->err($errorCode, $errorText);
        }

        $key = $request->post('key');
        $status = $request->post('status');
        try{
            $accountConfig = AccountConfig::find($uid);
            $accountConfig->$key = $status;
            $accountConfig->updated_at = time();
            $accountConfig->save();
            $cacheKey = 'account_config:' . $uid;
            Redis::setex($cacheKey,60,json_encode($accountConfig));
        }catch (\Exception $e){
            Log::error('hideTrace failed : '.$e->getMessage());
            $errorCode = 400;
            $errorText = 'Setting Failed !';
            return $this->err($errorCode, $errorText);
        }
        return $this->succ(['accountConfig'=>$accountConfig]);
    }


    //会员套餐列表
    public function membershipList(){
        $list = DB::table("membership")->selectRaw("id,name_cn,name_en,prize,auth")->get()->toArray();
        $membershipAuth = config('system.membership');
        foreach ($list as $v){
            $v->auths = [];
            if($v->auth){
                $authArray = array_filter(explode(',',$v->auth));
                foreach ($authArray as $authid){
                    $text = isset($membershipAuth[$authid]) ? $membershipAuth[$authid] : '';
                    $auth = ['id'=>$authid,'text'=>$text];
                    array_push($v->auths,$auth);
                }
            }
        }
        return $this->succ(['list'=>$list]);
    }


}