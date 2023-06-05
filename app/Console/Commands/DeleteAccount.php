<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AccountDelete;
use App\Models\Account;
use App\Models\AccountMobile;
use App\Models\AccountAnonymous;
use App\Models\AccountCertification;
use App\Models\AccountInfo;
use App\Models\AccountTag;
use App\Http\Library\TIM;
use Illuminate\Support\Facades\DB;


class DeleteAccount extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'DeleteAccount';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'DeleteAccount';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $days = 7;
        $result = AccountDelete::where('is_delete', 1)->where('delete_time', '<', date('Y-m-d H:i:s', time() - $days * 86400))->limit(100)->get();
        $imUserids = [];
        
        foreach ($result as $v) {
            $info = AccountInfo::where('uid', $v->uid)->first();
            if (!empty($info->im_userid)) {
                $imUserids[] = $info->im_userid;
            }
            Account::where('uid', $v->uid)->delete();
            AccountMobile::where('uid', $v->uid)->delete();
            AccountAnonymous::where('uid', $v->uid)->delete();
            AccountCertification::where('uid', $v->uid)->delete();
            AccountInfo::where('uid', $v->uid)->delete();
            AccountTag::where('uid', $v->uid)->delete();
            $tables = ['user_tag', 'user_dynamics', 'user_dynamic_video', 'user_dynamic_pictures', 'user_dynamic_likes', 'user_dynamic_comments'];
            foreach ($tables as $table) {
                DB::table($table)->where('uid', $v->uid)->delete();
            }

            AccountDelete::where('uid', $v->uid)->update(['is_delete' => 2]);
        }

        if (!empty($imUserids)) {
            $tim = new TIM;
            $tim->accountDelete($imUserids);
        }

    }
}