<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\Club;
use App\Models\AccountInfo;
use App\Http\Library\TIM;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\File;
class ClubController extends ApiController
{
    public function getList(Request $request)
    {
        $params = $request->all();
        $validator = Validator::make($params, [
            'name' => ['string', 'between:0,30'],
            'type' => ['required', 'integer', Rule::in([0, 1, 2, 3, 4])],
            'page' => ['required', 'integer', 'between:1,10'],
        ]);

        if ($validator->fails()) {
            $errorCode = 2000;
            $errorText = 'Data is not legal' . ':' . $validator->errors()->keys()[0] . ' ' . $validator->errors()->first();
            return $this->err($errorCode, $errorText);
        }

        $club = new Club;
        switch ($params['type']) {
            case 1:
                $club = $club->orderBy('club.id', 'DESC');
                break;
            case 2:
                $club = $club->orderBy('club.award', 'DESC');
                break;
            case 3:
                $club = $club->orderBy('club.7_day_award', 'DESC');
                break;
            case 4:
                $uid = $request->get('uid');
                $club = $club->where('club.uid', $uid);
                $club = $club->orderBy('club.id', 'DESC');
                break;
            default:
                if (!empty($params['name'])) {
                    // $club = $club->whereLike('name', $params['name']);
                    $club = $club->where('club.name', 'like', '%' . $params['name'] . '%');
                }
                $club = $club->orderBy('club.id', 'DESC');
                break;
        }

        $club = $club->select(
            'club.id',
            'club.im_groupid',
            'club.face_url',
            'club.name',
            'club.introduction',
            'club.notification',
            'club.uid',
            'club.award',
            'club.today_award',
            'account_info.nickname',
            'account_info.avatar',
        );
        $club = $club->leftjoin('account_info', 'account_info.uid', '=', 'club.uid');
        $club = $club->where('is_delete', 0);
        $query = $club->forPage($params['page'], 10);
        $data['list'] = $query->get()->toArray();

        foreach ($data['list'] as $k => $v) {
            $data['list'][$k]['merge_face_url'] = !empty($v['face_url']) ? $v['face_url'] : env('DOMAIN_API_HTTP', 'http') . '://' . env('DOMAIN_API') . '/api/club/mergeface/' . $v['id'] . '.jpg';
            // $data['list'][$k]['merge_face_url'] = env('DOMAIN_API_HTTP', 'http') . '://' . env('DOMAIN_API') . '/api/club/mergeface/' . $v['id'] . '.jpg';
            unset($data['list'][$k]['id']);
        }

        $attributes['total'] = $query->count();
        $attributes['page'] = $params['page'];
        $attributes['page_size'] = 10;
        $data['attributes'] = $attributes;
        return $this->succ($data);
    }

    public function add(Request $request)
    {
        $maxClub = 15;
        $uid = $request->get('uid');
        $params = $request->all();
        $validator = Validator::make($params, [
            'name' => ['required', 'string', 'between:1,30'],
            // 'type' => ['required', 'integer'],
            // 'page' => ['required', 'integer', 'between:1,99'],
            'introduction' => ['string', 'between:0,240'],
            'notification' => ['string', 'between:0,300'],
            'face_url' => ['string', 'between:0,100'],
            'uids' => ['array'],
            'random' => ['required', 'integer', 'min:0', 'max:4294967295'],
        ]);

        if ($validator->fails()) {
            $errorCode = 2010;
            $errorText = 'Data is not legal' . ':' . $validator->errors()->keys()[0] . ' ' . $validator->errors()->first();
            return $this->err($errorCode, $errorText);
        }

        $accountInfo = AccountInfo::where('uid', $uid)->first();
        if (empty($accountInfo->im_userid)) {
            $errorCode = 2011;
            $errorText = 'im_userid not bind';
            return $this->err($errorCode, $errorText);
        }

        if (Club::where('uid', $uid)->count() > $maxClub) {
            $errorCode = 2012;
            $errorText = 'Only ' . $maxClub . ' groups are allowed';
            return $this->err($errorCode, $errorText);
        }

        $memberList = [];
        foreach ($params['uids'] ?? [] as $uid) {
            $u = AccountInfo::where('uid', $uid)->orWhere('im_userid', $uid)->first();
            if (!empty($u) && !empty($u->im_userid)) {
                // $memberList[] = ["Member_Account" => $u->im_userid, "Role" => 'Admin'];
                $memberList[] = ["Member_Account" => $u->im_userid];
            }
        }

        $tim = new TIM;
        $res = $tim->createGroup([
            'Owner_Account' => $accountInfo->im_userid,
            "Type" => "Public",
            // "Type" => "Private",
            "Name" => $params['name'],
            "Introduction" => $params['introduction'] ?? '',
            "Notification" => $params['notification'] ?? '',
            "FaceUrl" => $params['face_url'] ?? '',
            "ApplyJoinOption" => 'NeedPermission',
            // "ApplyJoinOption" => 'FreeAccess',
            "MaxMemberCount" => 20,
            "MemberList" => $memberList,
        ]);

        if ($res[0]) {
            Club::create([
                'im_groupid' => $res[2]['GroupId'],
                'name' => $params['name'],
                'introduction' => $params['introduction'] ?? '',
                'notification' => $params['notification'] ?? '',
                'face_url' => $params['face_url'] ?? '',
                'uid' => $uid,
            ]);

            return $this->succ(['im_groupid' => $res[2]['GroupId']]);
        } else {
            $errorCode = 2013;
            $errorText = $res[2]['ErrorInfo'];
            return $this->err($errorCode, $errorText);
        }
    }

    public function add2(Request $request)
    {
        $maxClub = 15;
        $uid = $request->get('uid');
        $params = $request->all();
        $validator = Validator::make($params, [
            'name' => ['required', 'string', 'between:1,30'],
            'im_groupid' => ['required', 'string', 'between:1,20', 'unique:\App\Models\Club,im_groupid'],
            'introduction' => ['string', 'between:0,240'],
            'notification' => ['string', 'between:0,300'],
            'face_url' => ['string', 'between:0,100'],
            'random' => ['required', 'integer', 'min:0', 'max:4294967295'],
        ]);

        if ($validator->fails()) {
            $errorCode = 2080;
            $errorText = 'Data is not legal' . ':' . $validator->errors()->keys()[0] . ' ' . $validator->errors()->first();
            return $this->err($errorCode, $errorText);
        }

        $accountInfo = AccountInfo::where('uid', $uid)->first();
        if (empty($accountInfo->im_userid)) {
            $errorCode = 2081;
            $errorText = 'im_userid not bind';
            return $this->err($errorCode, $errorText);
        }

        if (Club::where('uid', $uid)->count() > $maxClub) {
            $errorCode = 2082;
            $errorText = 'Only ' . $maxClub . ' groups are allowed';
            return $this->err($errorCode, $errorText);
        }
        $tim = new TIM;
        $res = $tim->getGroupMemberInfo($params['im_groupid'], 0, 1);
        if ($res[0]) {
            Club::create([
                'im_groupid' => $params['im_groupid'],
                'name' => $params['name'],
                'introduction' => $params['introduction'] ?? '',
                'notification' => $params['notification'] ?? '',
                'face_url' => $params['face_url'] ?? '',
                'uid' => $uid,
            ]);

            return $this->succ(['im_groupid' => $params['im_groupid']]);
        } else {
            $errorCode = 2083;
            $errorText = $res[2]['ErrorInfo'];
            return $this->err($errorCode, $errorText);
        }
    }

    public function info(Request $request)
    {
        $params = $request->all();
        $validator = Validator::make($params, [
            'im_groupid' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            $errorCode = 2031;
            $errorText = 'Data is not legal' . ':' . $validator->errors()->keys()[0] . ' ' . $validator->errors()->first();
            return $this->err($errorCode, $errorText);
        }

        $im_groupid = $params['im_groupid'];

        $club = new Club;
        $club = $club->where('im_groupid', $im_groupid);
        $club = $club->select(
            // 'club.id',
            'club.im_groupid',
            'club.face_url',
            'club.name',
            'club.introduction',
            'club.notification',
            'club.uid',
            'club.award',
            'club.today_award',
            'account_info.nickname',
            'account_info.avatar',
        );
        $club = $club->leftjoin('account_info', 'account_info.uid', '=', 'club.uid');
        $club = $club->where('is_delete', 0)->first();

        if (empty($club)) {
            $errorCode = 2030;
            $errorText = 'Club does not exist';
            return $this->err($errorCode, $errorText);
        } else {
            return $this->succ($club);
        }
    }

    public function destroys(Request $request)
    {
        $params = $request->all();
        $validator = Validator::make($params, [
            'im_groupid' => ['required', 'string'],
            'random' => ['required', 'integer', 'min:0', 'max:4294967295'],
        ]);

        if ($validator->fails()) {
            $errorCode = 2041;
            $errorText = 'Data is not legal' . ':' . $validator->errors()->keys()[0] . ' ' . $validator->errors()->first();
            return $this->err($errorCode, $errorText);
        }

        $im_groupid = $params['im_groupid'];
        // echo $im_groupid;exit;
        $uid = $request->get('uid');
        $club = new Club;
        $res = $club->where('im_groupid', $im_groupid)
            ->where('uid', $uid)
            ->where('is_delete', 0)
            ->first();

        if (empty($res)) {
            $errorCode = 2040;
            $errorText = 'club does not exist';
            return $this->err($errorCode, $errorText);
        }

        // $tim = new TIM;
        // $res = $tim->getAppidGroupList();
        // dd($res);
        // if ($res[0]) {
        //     foreach ($res[2]['GroupIdList'] as $v) {
        //         $res2 = $tim->destroyGroup($v['GroupId']);
        //         // dd($res2);
        //     }
        // }
        // exit;
        $club = new Club;
        $club->where('im_groupid', $im_groupid)
            ->where('uid', $uid)
            ->update(['is_delete' => 1]);

        $tim = new TIM;
        $res = $tim->destroyGroup($im_groupid);

        if ($res[0]) {
            return $this->succ([]);
        } else {
            $errorCode = 2041;
            $errorText = $res[2]['ErrorInfo'];
            return $this->err($errorCode, $errorText);
        }
    }

    public function quit(Request $request)
    {
        $uid = $request->get('uid');
        $params = $request->all();
        $validator = Validator::make($params, [
            'im_userid' => ['required', 'string'],
            'im_groupid' => ['required', 'string'],
            'random' => ['required', 'integer', 'min:0', 'max:4294967295'],
        ]);

        if ($validator->fails()) {
            $errorCode = 2020;
            $errorText = 'Data is not legal' . ':' . $validator->errors()->keys()[0] . ' ' . $validator->errors()->first();
            return $this->err($errorCode, $errorText);
        }

        $im_groupid = $params['im_groupid'];
        $accountInfo = AccountInfo::where('uid', $uid)->first();
        if (empty($accountInfo->im_userid)) {
            $errorCode = 2021;
            $errorText = 'im_userid not bind';
            return $this->err($errorCode, $errorText);
        }

        $club = Club::where('im_groupid', $im_groupid)->first();
        if (empty($club)) {
            $errorCode = 2022;
            $errorText = 'Club does not exist';
            return $this->err($errorCode, $errorText);
        }

        $tim = new TIM;
        if ($params['im_userid'] != $params['im_userid']) {
            $res = $this->getRoleInGroup($im_groupid, $accountInfo->im_userid);

            if ($res[0]) {
                $errorCode = 2023;
                $errorText = $res[2]['ErrorInfo'];
                return $this->err($errorCode, $errorText);
            }

            if ($res[0] && !in_array($res[2]['UserIdList'][0]['Role'], ['Owner', 'Admin'])) {
                $errorCode = 2024;
                $errorText = 'Not allowed to exit';
                return $this->err($errorCode, $errorText);
            }
        }

        if ($uid == $club->uid) {
            $errorCode = 2025;
            $errorText = 'The group owner is not allowed to quit';
            return $this->err($errorCode, $errorText);
        }

        $res = $tim->deleteGroupMember($im_groupid, $params['im_userid']);
        if ($res[0]) {
            return $this->succ([]);
        } else {
            $errorCode = 2026;
            $errorText = $res[2]['ErrorInfo'];
            return $this->err($errorCode, $errorText);
        }
    }

    public function edit(Request $request)
    {
        $uid = $request->get('uid');
        $params = $request->all();
        $validator = Validator::make($params, [
            'name' => ['string', 'between:1,30'],
            'im_groupid' => ['required', 'string'],
            'introduction' => ['string', 'between:0,240'],
            'notification' => ['string', 'between:0,300'],
            'face_url' => ['string', 'between:0,100'],
            'random' => ['required', 'integer', 'min:0', 'max:4294967295'],
        ]);

        if ($validator->fails()) {
            $errorCode = 2050;
            $errorText = 'Data is not legal' . ':' . $validator->errors()->keys()[0] . ' ' . $validator->errors()->first();
            return $this->err($errorCode, $errorText);
        }

        $im_groupid = $params['im_groupid'];
        $accountInfo = AccountInfo::where('uid', $uid)->first();
        if (empty($accountInfo->im_userid)) {
            $errorCode = 2051;
            $errorText = 'im_userid not bind';
            return $this->err($errorCode, $errorText);
        }

        $club = new Club;
        $res = $club->where('im_groupid', $im_groupid)
            ->where('uid', $uid)
            ->where('is_delete', 0)
            ->first();

        if (empty($res)) {
            $errorCode = 2040;
            $errorText = 'club does not exist';
            return $this->err($errorCode, $errorText);
        }

        $tim = new TIM;
        $res = $tim->modifyGroupBaseInfo([
            'GroupId' => $im_groupid,
            // "Type" => "Public",
            "Name" => $params['name'],
            "Introduction" => $params['introduction'] ?? '',
            "Notification" => $params['notification'] ?? '',
            "FaceUrl" => $params['face_url'] ?? '',
            // "ApplyJoinOption" => 'NeedPermission',
            // "MaxMemberCount" => 20,
            // "MemberList" => [
            //     ["Member_Account" => $accountInfo->im_userid, "Role" => 'Admin']
            // ],
        ]);

        if ($res[0]) {
            Club::where('im_groupid', $im_groupid)->where('uid', $uid)->update([
                'name' => $params['name'],
                'introduction' => $params['introduction'] ?? '',
                'notification' => $params['notification'] ?? '',
                'face_url' => $params['face_url'] ?? '',
            ]);

            return $this->succ([]);
        } else {
            $errorCode = 2013;
            $errorText = $res[2]['ErrorInfo'];
            return $this->err($errorCode, $errorText);
        }
    }

    public function mergeFace(Request $request, $id)
    {
        $redis = Redis::connection('cache');
        $id = intval($id);
        $m = $redis->get('club:' . $id);
        $storagePath = storage_path('club/mergeface');
        $savePath = storage_path('club/mergeface/' . $id . '.jpg');
        $test = true;
        if (empty($m)) {

            if (!File::isDirectory($storagePath)) {
                File::makeDirectory($storagePath, 0777, true, true);
            }

            $tim = new TIM;
            $club = Club::where('id', $id)->first();
            if (!empty($club)) {
                $time = $test == true ? 60 : 3600 * 3 + rand(100, 1000);
                $redis->setex('club:' . $id, $time, 1);
                $res = $tim->getGroupMemberInfo($club->im_groupid, 0, 6);
                $imUserids = [];
                if ($res[0]) {
                    
                    foreach ($res[2]['MemberList'] ?? [] as $v) {
                        $imUserids[] = $v['Member_Account'];
                    }

                    $ats = AccountInfo::where('uid', $club->uid)->orWhereIn('im_userid', $imUserids)->where('avatar', '!=', '')->pluck('avatar')->toArray();
                    $club->mergeFace($ats, $savePath);
                }


            }
        }

        return response()->download($savePath);
    }

    public function addGroupMember(Request $request)
    {
        $params = $request->all();
        $validator = Validator::make($params, [
            'im_groupid' => ['required', 'string'],
            'uids' => ['required', 'array'],
            'random' => ['required', 'integer', 'min:0', 'max:4294967295'],
        ]);

        if ($validator->fails()) {
            $errorCode = 2060;
            $errorText = 'Data is not legal' . ':' . $validator->errors()->keys()[0] . ' ' . $validator->errors()->first();
            return $this->err($errorCode, $errorText);
        }

        $im_groupid = $params['im_groupid'];
        $memberList = [];
        foreach ($params['uids'] ?? [] as $uid) {
            $u = AccountInfo::where('uid', $uid)->orWhere('im_userid', $uid)->first();
            if (!empty($u) && !empty($u->im_userid)) {
                // $memberLists[] = ["Member_Account" => $u->im_userid, "Role" => 'Admin'];
                $memberList[] = ["Member_Account" => $u->im_userid];
            }
        }

        $tim = new TIM;
        $res = $tim->addGroupMember($im_groupid, $memberList);
        if ($res[0]) {
            return $this->succ($res[2]);
        } else {
            $errorCode = 2061;
            $errorText = $res[2]['ErrorInfo'];
            return $this->err($errorCode, $errorText);
        }
    }

    public function getMemberList(Request $request)
    {
        $params = $request->all();
        $validator = Validator::make($params, [
            'im_groupid' => ['required', 'string'],
            'limit' => ['required', 'integer'],
            'offset' => ['required', 'integer'],
        ]);

        if ($validator->fails()) {
            $errorCode = 2070;
            $errorText = 'Data is not legal' . ':' . $validator->errors()->keys()[0] . ' ' . $validator->errors()->first();
            return $this->err($errorCode, $errorText);
        }

        $im_groupid = $params['im_groupid'];
        $tim = new TIM;
        $res = $tim->getGroupMemberInfo($im_groupid, $params['limit'], $params['offset']);
        if ($res[0]) {
            return $this->succ($res[2]);
        } else {
            $errorCode = 2071;
            $errorText = $res[2]['ErrorInfo'];
            return $this->err($errorCode, $errorText);
        }
    }
}
