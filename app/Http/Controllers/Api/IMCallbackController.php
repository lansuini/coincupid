<?php

namespace App\Http\Controllers\Api;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Club;
use App\Models\AccountInfo;
use App\Http\Library\TIM;
use App\Jobs\IMCallbackJob;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\File;
use Aws\S3\S3Client;

class IMCallbackController extends Controller
{
    public function index(Request $request)
    {
        // Log::debug('IMCallback', [$request->query(), $request->post()]);
        // Log::debug('IMCallback2', [$request->post()]);
        // $callbackCommand = $request->input('CallbackCommand');
        // Log::debug('IMCallback3', [$callbackCommand]);

        // switch ($callbackCommand) {

        //         /*
        //     [{"CallbackCommand":"Group.CallbackAfterCreateGroup","EventTime":1682914763569,"GroupId":"@TGS#2Z4CLQE5CM","InviteOption":"NeedPermission",
        //         "MemberList":[{"Member_Account":"PLNJEE0k"}],"Name":"test8888","Operator_Account":"PLNJEE0k","Owner_Account":"PLNJEE0k","Type":"Public"}]
        //     */
        //     case 'Group.CallbackAfterCreateGroup':
        //         // $groupId = $request->input('GroupId');
        //         $this->callbackAfterCreateGroup($request);
        //         break;

        //         /*
        //     [{"CallbackCommand":"Group.CallbackAfterNewMemberJoin","EventTime":1682914665661,"GroupId":"WorldGroup777",
        //         "JoinType":"Apply","NewMemberList":[{"Member_Account":"PLNJEE0k"}],"Operator_Account":"PLNJEE0k","Type":"AVChatRoom"}]
        //     */
        //     case 'Group.CallbackAfterNewMemberJoin':
        //         $this->callbackAfterNewMemberJoin($request);
        //         break;
        // }

        // if ($callbackCommand == 'Group.CallbackAfterNewMemberJoin') {
        //     $groupId = $request->input('GroupId');
        //     Log::debug('IMCallback|#2', [$callbackCommand, $groupId]);
        // }

        IMCallbackJob::dispatch($request->all())->onConnection('redis')->onQueue('imcallback');
        // IMCallbackJob::dispatch($request->all());
        return [
            "ActionStatus" => "OK",
            "ErrorInfo" => "",
            "ErrorCode" => 0
        ];
    }



    public function test(Request $request) {
        // phpinfo();
    }
}
