<?php

namespace App\Http\Library;

use Hashids\Hashids;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\DB;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Validator;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

/**
 * https://cloud.tencent.com/document/product/269/2282
 */
class TIM
{
    /**
     {
        "UserID":"test",
        "Nick":"test",
        "FaceUrl":"http://www.qq.com"
        }
     */
    public function accountImport($userId, $nick, $faceUrl)
    {
        $args = [];
        $args['method'] = 'v4/im_open_login_svc/account_import';
        $args['data'] = [
            "UserID" => (string) $userId,
            "Nick" => (string) $nick,
            "FaceUrl" => (string) $faceUrl,
        ];

        return $this->request($args);
    }

    /**
     * @example {
        "DeleteItem":
        [
            {
                "UserID":"UserID_1"
            },
            {
                "UserID":"UserID_2"
            }
        ]
        }
     * @link https://cloud.tencent.com/document/product/269/36443
     */
    public function accountDelete($userId)
    {
        $userIds = (array) $userId;
        $struct = [];
        foreach ($userIds as $id) {
            $struct[] = ['UserID' => $id];
        }
        $args = [];
        $args['method'] = 'v4/im_open_login_svc/account_delete';
        $args['data'] = [
            'DeleteItem' => $struct
        ];
        return $this->request($args);
    }

    /**
     * @example {
    "Owner_Account": "leckie", // 群主的 UserId（选填）
    "Type": "Public", // 群组类型：Private/Public/ChatRoom/AVChatRoom/Community
    "Name": "TestGroup" // 群名称（必填）
}
     * @example {
    "Owner_Account": "leckie", // 群主的 UserId（选填）
    "Type": "Public", // 群组类型：Private/Public/ChatRoom/AVChatRoom/Community
    "Name": "TestGroup", // 群名称（必填）
    "Introduction": "This is group Introduction", // 群简介（选填）
    "Notification": "This is group Notification", // 群公告（选填）
    "FaceUrl": "http://this.is.face.url", // 群头像 URL（选填）
    "MaxMemberCount": 500, // 最大群成员数量（选填）
    "ApplyJoinOption": "FreeAccess"  // 申请加群处理方式（选填）
}
     * @example 
     * @link https://cloud.tencent.com/document/product/269/1615
     */
    public function createGroup($struct)
    {
        $args = [];
        $args['method'] = 'v4/group_open_http_svc/create_group';
        $args['data'] = $struct;
        return $this->request($args);
    }

    /**
     * @link https://cloud.tencent.com/document/product/269/1624
     */
    public function destroyGroup($groupId)
    {
        $args = [];
        $args['method'] = 'v4/group_open_http_svc/destroy_group';
        $args['data'] = ['GroupId' => $groupId];
        return $this->request($args);
    }

    /**
     * @link https://cloud.tencent.com/document/product/269/1620
     */
    public function modifyGroupBaseInfo($data)
    {
        $args = [];
        $args['method'] = 'v4/group_open_http_svc/modify_group_base_info';
        $args['data'] = $data;
        return $this->request($args);
    }

    /**
     * @example {
    "GroupId":"@TGS#1NVTZEAE4", // 群组 ID（必填）
    "Limit": 100, // 最多获取多少个成员的资料
    "Offset": 0 // 从第多少个成员开始获取资料
    }

     * @link https://cloud.tencent.com/document/product/269/1617
     */
    public function getGroupMemberInfo($groupId, $limit, $offset, $extend = [])
    {
        $args = [];
        $args['method'] = 'v4/group_open_http_svc/get_group_member_info';
        $args['data'] = array_merge([
            'GroupId' => $groupId,
            'Limit' => $limit,
            'Offset' => $offset,
        ], $extend);
        return $this->request($args);
    }

    /**
     * @link https://cloud.tencent.com/document/product/269/1621
     */
    public function addGroupMember($groupId, $memberList)
    {
        $args = [];
        $args['method'] = 'v4/group_open_http_svc/add_group_member';
        $args['data'] = [
            'GroupId' => $groupId,
            'MemberList' => $memberList
        ];
        return $this->request($args);
    }

    public function deleteGroupMember($groupId, $userId, $silence = null, $reason = null)
    {
        $args = [];
        $args['method'] = 'v4/group_open_http_svc/delete_group_member';
        $params = ['GroupId' => $groupId, 'MemberToDel_Account' => (array) $userId];
        if (!empty($silence) && $silence == 1) {
            $params['silence'] = 1;
        }

        if (!empty($reason)) {
            $params['reason'] = $reason;
        }
        $args['data'] =  $params;
        return $this->request($args);
    }

    /**
     * @link https://cloud.tencent.com/document/product/269/1622
     */
    public function getRoleInGroup($groupId, $userId)
    {
        $args = [];
        $args['method'] = 'v4/group_open_http_svc/get_role_in_group';
        $args['data'] = ['GroupId' => $groupId, 'User_Account' => (array) $userId];
        return $this->request($args);
    }

    /**
     * @link https://cloud.tencent.com/document/product/269/1614
     */
    public function getAppidGroupList($limit = 1000, $next = 0) 
    {
        $args = [];
        $args['method'] = 'v4/group_open_http_svc/get_appid_group_list';
        $args['data'] = ['Limit' => $limit, 'next' => $next];
        return $this->request($args);
    }

    /**
     * @example {
    "From_Account":"id",
    "AddFriendItem":
    [
        {
            "To_Account":"id1",
            "AddSource":"AddSource_Type_XXXXXXXX"
        }
    ]
}
     * @example {
    "From_Account":"id",
    "AddFriendItem":
    [
        {
            "To_Account":"id1",
            "Remark":"remark1",
            "GroupName":"同学", // 添加好友时只允许设置一个分组，因此使用 String 类型即可
            "AddSource":"AddSource_Type_XXXXXXXX",
            "AddWording":"I'm Test1"
        }
    ],
    "AddType":"Add_Type_Both",
    "ForceAddFlags":1
}
     * @example {
    "From_Account":"id",
    "AddFriendItem":
    [
        {
            "To_Account":"id1",
            "AddSource":"AddSource_Type_XXXXXXXX"
        },
        {
            "To_Account":"id2",
            "Remark":"remark2",
            "GroupName":"同学", // 添加好友时只允许设置一个分组，因此使用 String 类型即可
            "AddSource":"AddSource_Type_XXXXXXXX",
            "AddWording":"I'm Test2"
        },
        {
            "To_Account":"id3",
            "Remark":"remark3",
            "GroupName":"同事", // 添加好友时只允许设置一个分组，因此使用 String 类型即可
            "AddSource":"AddSource_Type_XXXXXXXX",
            "AddWording":"I'm Test3"
        }
    ],
    "AddType":"Add_Type_Both",
    "ForceAddFlags":1
}
     * @link https://cloud.tencent.com/document/product/269/1643
     */
    public function friendAdd($data)
    {
        $args = [];
        $args['method'] = 'v4/sns/friend_add';
        $args['data'] = $data;
        return $this->request($args);
    }

    /**
     * @link https://cloud.tencent.com/document/product/269/1646
     */
    public function friendCheck($fromAccount, $toAccount, $checkType = 'CheckResult_Type_Both')
    {
        $args = [];
        $args['method'] = 'v4/sns/friend_check';
        $args['data'] = [
            "From_Account" => $fromAccount,
            "To_Account" => (array) $toAccount,
            "CheckType" => $checkType,
        ];

        return $this->request($args);
    }

    /**
     * @link https://cloud.tencent.com/document/product/269/1647
     */
    public function friendGet($fromAccount, $startIndex = 0, $standardSequence = 0, $customSequence = 0)
    {
        $args = [];
        $args['method'] = 'v4/sns/friend_get';
        $args['data'] = [
            "From_Account" => $fromAccount,
            "StartIndex" => $startIndex,
            "StandardSequence" => $standardSequence,
            "CustomSequence" => $customSequence,
        ];
        return $this->request($args);
    }

    /**
     * @link https://cloud.tencent.com/document/product/269/8609
     */
    public function friendGet2($fromAccount, $toAccount, $tagList = [])
    {
        $args = [];
        $args['method'] = 'v4/sns/friend_get';
        $args['data'] = [
            "From_Account" => $fromAccount,
            "To_Account" => $toAccount,
            "TagList" => $tagList,
        ];
        return $this->request($args);
    }

    protected function request($args)
    {
        $defs = [];
        $defs['contenttype'] = 'json';
        $defs['path'] = 'adminapisgp.im.qcloud.com';
        // $defs['method'] = 'v4/group_open_http_svc/get_appid_group_list';
        $defs['sdkappid'] = env('IM_APPID');
        $defs['identifier'] = env('IM_ADMIN');
        $defs['usersig'] = $this->getUserSig(env('IM_ADMIN'));
        $defs['random'] = rand(0, 4294967295);
        $args = array_merge($defs, $args);
        $client = new Client([
            'timeout'  => env('API_REQUEST_TIME_OUT', 8.0),
            'headers' => [
                'User-Agent' => env('API_REQUEST_NAME', 'Coincupid'),
            ]
        ]);
        $sMethod = 'POST';
        $sParams = ['json' => $args['data']];
        $sUrl = "https://{$args['path']}/{$args['method']}?sdkappid={$args['sdkappid']}&identifier={$args['identifier']}&usersig={$args['usersig']}&random={$args['random']}&contenttype={$args['contenttype']}";
        try {
            $response = $client->request($sMethod, $sUrl, $sParams);
            $sCode = $response->getStatusCode();
            $sResponse = (string) $response->getBody()->getContents();
            $res = json_decode($sResponse, true);
            if (isset($res['ErrorCode']) && $res['ErrorCode'] == 0) {
                Log::info('IM info', [$sMethod, $sUrl, $sParams, $sCode, $res]);
                return [true, $res['ErrorInfo'] ?? 'success', $res];
            } else {
                Log::error('IM error#1', [$sMethod, $sUrl, $sParams, $sCode, $res]);
                return [false, $res['ErrorInfo'] ?? 'ErrorInfo is empty', $res];
            }
        } catch (GuzzleException $e) {
            $m1 = Psr7\Message::toString($e->getRequest());
            $m2 = $e->getMessage();
            Log::error('IM error#2', [$sMethod, $sUrl, $sParams, $m1, $m2]);
            return [false, $m2, $res];
        }
    }

    public function getUserSig($userId)
    {
        $api = new \Tencent\TLSSigAPIv2(env('IM_APPID'), env('IM_KEY'));
        return $api->genUserSig($userId);
    }
}
