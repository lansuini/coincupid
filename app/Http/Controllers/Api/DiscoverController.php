<?php
/**
 * Created by PhpStorm.
 * User: luobinhan
 * Date: 2023/2/19
 * Time: 3:43
 */

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use App\Models\AccountInfo;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\DB;
class DiscoverController extends ApiController
{
    //人气投票
    public function popularUser(Request $request){
        $uid = $request->get('uid');
        $popCount = DB::table("account_info")->where("verified",1)->count("uid");
        if($popCount == 0){
            return $this->succ(["newCount"=>0,"userInfo"=>[]]);
        }
        $userVotes = DB::table("user_votes")->where("vote_uid",$uid)->pluck("pop_uid");
        $voteCount = count($userVotes);
        if($popCount <= $voteCount){
            return $this->succ(["newCount"=>0,"userInfo"=>[]]);
        }
        $newCount = $popCount - $voteCount;
        $randomUser = DB::table("account_info")
            ->where("uid","<>",$uid)
            ->where("verified",1)
            ->whereNotIn("uid",$userVotes)
            ->inRandomOrder()
            ->first();
        $accountCertification = DB::table("account_certification")->where("uid",$randomUser->uid)->first();
        $randomUser->accountCertification = $accountCertification;
        return $this->succ(["newCount"=>$newCount,"userInfo"=>$randomUser]);
    }

    public function popularVote(Request $request,$uuid){
        $uid = $request->get('uid');

        $popUser = DB::table("account_info")->find($uuid);
        if(!$popUser){
            return $this->succ([]);
        }
        try {
            DB::beginTransaction();
            DB::table("account_info")->where("uid", $uid)->increment("popularity",1);
            DB::table("user_votes")->insert(["pop_uid"=>$uuid,"vote_uid"=>$uid,"created_at"=>time()]);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::Error("$uid 人气投票失败：", $e->getMessage());
            return $this->err(400, "投票失败");
        }

        return $this->succ([]);

    }

    public function getDynamics(Request $request){

        $uid = $request->get('uid');
        $params = $request->all();
        $page = isset($params['page']) && !empty($params['page']) && is_numeric($params['page']) ? $params['page'] : 1;
        $page_size = isset($params['page_size']) && !empty($params['page_size']) && is_numeric($params['page_size']) ? $params['page'] : 10;

            $query = DB::table('user_dynamics')->whereRaw('deleted_at is null')->where('auth',3);


        $dynamics = $query->forPage($page,$page_size)->orderByDesc('created_at')->get()->toArray();

        foreach ($dynamics as $dynamic){
            $userInfo = DB::table('account_info as a')->leftJoin('account as b','a.uid','=','b.uid')->selectRaw("a.nickname,a.avatar,a.verified,b.vip_level,a.popularity,a.birthday,a.constellation,a.city")->where('a.uid',$dynamic->uid)->first();
            $dynamic->user_info = $userInfo;
            $dynamic->pictures = [];
            if($dynamic->type == 1){
                $pics = DB::table('user_dynamic_pictures')->where('dynamic_id',$dynamic->id)->pluck('picture');
                $dynamic->pictures = $pics ?? [];
            }
            $commentList = DB::table('user_dynamic_comments as a')->leftJoin('account_info as b','a.uid','=','b.uid')->where('a.dynamic_id',$dynamic->id)->limit(10)->get(["a.id","a.uid","a.dynamic_id","a.content","b.nickname","a.created_at",])->toArray();
            $dynamic->comment_list = $commentList;
            $islike = DB::table('user_dynamic_likes')->where('dynamic_id',$dynamic->id)->where('uid',$uid)->exists();
            $dynamic->is_like = $islike;
        }
        $data['list'] = $dynamics;

        $attributes['total'] = $query->count();
        $attributes['page'] = $page;
        $attributes['page_size'] = $page_size;
        $data['attributes'] = $attributes;
        return $this->succ($data);
    }

    //人气榜单
    public function popUserList(Request $request){

        $uid = $request->get('uid');

        $params = $request->all();
        $validator = Validator::make($params, [
            'page' => [ 'required','integer', 'between:1,150'],
            'page_size' => ['integer', 'between:3,50'],
        ]);
        if ($validator->fails()) {
            $errorCode = 1070;
            $errorText = 'Data is not legal' . ':' . $validator->errors()->keys()[0] . ' ' . $validator->errors()->first();
            return $this->err($errorCode, $errorText);
        }
        $page_size = isset($params['page_size']) ? $params['page_size'] : 3;
        $query = AccountInfo::where('uid', '!=', $uid);
        $users = $query
            ->orderByDesc("popularity")
            ->get(['nickname','uid','avatar','popularity','im_userid'])
            ->forPage($params['page'],$page_size);


        $attributes['total'] = $query->count();
        $attributes['page'] = (int)$params['page'];
        $attributes['page_size'] = $page_size;

        $data['list'] = $users;
        $data['attributes'] = $attributes;
        return $this->succ($data);
    }

    public function getVoteUsersByUid(Request $request,$uuid){
        $uid = $request->get('uid');
        $page = isset($params['page']) && !empty($params['page']) && is_numeric($params['page']) ? $params['page'] : 1;
        $page_size = isset($params['page_size']) && !empty($params['page_size']) && is_numeric($params['page_size']) ? $params['page'] : 10;
        $query = DB::table("user_votes as a")->leftJoin("account_info as b",'a.vote_uid','=','b.uid')->where("pop_uid",$uuid);

        $voteUsers = $query->selectRaw("b.uid,b.avatar,b.nickname")->orderByDesc("created_at")->forPage($page,$page_size)->get()->toArray();

        $data['list'] = $voteUsers;
        $attributes['total'] = $query->count();
        $attributes['page'] = $page;
        $attributes['page_size'] = $page_size;
        $data['attributes'] = $attributes;
        return $this->succ($data);
    }
}