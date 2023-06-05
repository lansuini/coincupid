<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Http\Library\MerchantCB;
use App\Models\ServerRequestLog;
use Illuminate\Support\Facades\Log;
use Artisan;

class SendMessage2 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'SendMessage2 {text} {telegram_robot_token} {telegram_robot_chat_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send message notify';


    public function handle()
    {
        $text = $this->argument('text');
        $scriptAddr = base_path() . '/app/Http/Library/telegram_notify.py';
        $token = $this->argument('telegram_robot_token');
        $id = $this->argument('telegram_robot_chat_id');
        if (!empty($token) && !empty($id)) {
            $text = str_replace("'", "`", $text);
            $script = "python3 $scriptAddr $token $id '$text'";
            exec($script, $result);
            Log::info('SendMessage', [$script, $result]);
        }
    }
}
