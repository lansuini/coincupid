<?php

namespace App\Console\Commands;
use App\Http\Library\TIM;
use Illuminate\Console\Command;

use Artisan;

class CreateGroup2 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'CreateGroup2';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'CreateGroup2';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $tim = new TIM;

        // dd([
        //     'GroupId' => 'WorldGroup',
        //     'Owner_Account' => env('IM_ADMIN'),
        //     "Type" => "Community",
        //     "Name" => 'World Group',
        //     "Introduction" => 'World Group',
        //     "Notification" => 'World Group',
        //     "FaceUrl" => '',
        //     "ApplyJoinOption" => 'FreeAccess',
        //     // "MaxMemberCount" => 10000,
        //     "MemberList" => [
        //         // ["Member_Account" => $accountInfo->im_userid, "Role" => 'Admin']
        //     ],
        // ]);
        $res = $tim->createGroup([
            'GroupId' => 'WorldGroup',
            'Owner_Account' => env('IM_ADMIN'),
            "Type" => "Community",
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
        echo 1;
        // dd($res);
    }

}