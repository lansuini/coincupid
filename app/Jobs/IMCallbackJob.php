<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Models\Club;
use App\Models\AccountInfo;
use App\Http\Library\TIM;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\File;
use Aws\S3\S3Client;
use Illuminate\Http\Request;

// use Illuminate\Support\Facades\Log;
use Artisan;

class IMCallbackJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $payload;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($payload)
    {
        $this->payload = $payload;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {   
        $request = $this->payload;
        Log::debug('IMCallback2', [$request->post()]);
        $callbackCommand = $request['CallbackCommand'] ?? '';
        // Log::debug('IMCallback3', [$callbackCommand]);

        switch ($callbackCommand) {

                /*
            [{"CallbackCommand":"Group.CallbackAfterCreateGroup","EventTime":1682914763569,"GroupId":"@TGS#2Z4CLQE5CM","InviteOption":"NeedPermission",
                "MemberList":[{"Member_Account":"PLNJEE0k"}],"Name":"test8888","Operator_Account":"PLNJEE0k","Owner_Account":"PLNJEE0k","Type":"Public"}]
            */
            case 'Group.CallbackAfterCreateGroup':
                // $groupId = $request->input('GroupId');
                $this->createMergeImage($request);
                break;

                /*
            [{"CallbackCommand":"Group.CallbackAfterNewMemberJoin","EventTime":1682914665661,"GroupId":"WorldGroup777",
                "JoinType":"Apply","NewMemberList":[{"Member_Account":"PLNJEE0k"}],"Operator_Account":"PLNJEE0k","Type":"AVChatRoom"}]
            */
            case 'Group.CallbackAfterNewMemberJoin':
                $this->createMergeImage($request);
                break;
        }
    }

    // protected function callbackAfterCreateGroup(Request $request)
    // {
    // }

    protected function createMergeImage($args)
    {
        $redis = Redis::connection('cache');
        $groupId = $args['GroupId'];
        $club = Club::where('im_groupid', $groupId)->first();
        $tim = new TIM;
        $storagePath = storage_path('club/mergeface');

        if (!File::isDirectory($storagePath)) {
            File::makeDirectory($storagePath, 0777, true, true);
        }

        if (!empty($club)) {
            $id = $club->id;
            
            $m = $redis->get('club:' . $id);
            if (!empty($m)) {
                return;
            }
            
            $savePath = storage_path('club/mergeface/' . $id . '.jpg');
            $time = 3600 * 3 + rand(100, 1000);

            $res = $tim->getGroupMemberInfo($club->im_groupid, 0, 6);
            $imUserids = [];
            
            if ($res[0] || true) {

                foreach ($res[2]['MemberList'] ?? [] as $v) {
                    $imUserids[] = $v['Member_Account'];
                }

                if (count($imUserids) >= 6) {
                    $redis->setex('club:' . $id, $time, 1);
                }
                
                $ats = AccountInfo::where('uid', $club->uid)->orWhereIn('im_userid', $imUserids)->where('avatar', '!=', '')->pluck('avatar')->toArray();
                // print_r($ats);
                // exit;
                $club->mergeFace($ats, $savePath);
                // echo 1;exit;
                $s3client = new S3Client(['region' => env('AWS_DEFAULT_REGION', 'ap-southeast-1'), 'version' => 'latest']);
                $bucket = env('AWS_BUCKET', 'im-project');
                $target = 'club/' . $id . '.jpg';

                // print_r([
                //     'Bucket' => $bucket,
                //     'Key' => $target,
                //     'ACL' => 'public-read',
                //     'SourceFile' => $savePath
                // ]);
                // exit;
                $s3Return = $s3client->putObject([
                    'Bucket' => $bucket,
                    'Key' => $target,
                    'ACL' => 'public-read',
                    'SourceFile' => $savePath
                ]);


                if ($s3Return['@metadata']['statusCode'] == 200) {
                    $res = $tim->modifyGroupBaseInfo([
                        'GroupId' =>  $groupId,
                        // "Type" => "Public",
                        // "Name" => $params['name'],
                        // "Introduction" => $params['introduction'] ?? '',
                        // "Notification" => $params['notification'] ?? '',
                        "FaceUrl" => $s3Return['@metadata']['effectiveUri'],
                        // "ApplyJoinOption" => 'NeedPermission',
                        // "MaxMemberCount" => 20,
                        // "MemberList" => [
                        //     ["Member_Account" => $accountInfo->im_userid, "Role" => 'Admin']
                        // ],
                    ]);


                }

                $club->face_url =  $s3Return['@metadata']['effectiveUri'];
                $club->save();
                
            }

        }
    }
}
