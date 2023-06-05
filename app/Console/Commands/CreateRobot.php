<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Http\Library\MerchantCB;
use App\Models\ServerRequestLog;
use Illuminate\Support\Facades\Log;
use App\Models\Account;

class CreateRobot extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'CreateRobot {country}{sex} {ageStart} {ageEnd}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create Robot User';


    public function handle()
    {
        $country = $this->argument('country');
        $sex = $this->argument('sex');
        $ageStart = $this->argument('ageStart');
        $ageEnd = $this->argument('ageEnd');
        Account::createRobotUser($country,$sex,$ageStart,$ageEnd);
    }
}
