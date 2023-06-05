<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\TransferInOut;
use App\Http\Library\MerchantCF;
use App\Models\Customer;
use Artisan;

class CreateTable extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'CreateTable {task}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'CreateTable';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $task = $this->argument('task');
        if ($task == 'tio') {
            $this->tio();
        }
    }

    protected function tio() {
        $transferInOut = new TransferInOut;
        $transferInOut->retryCreateAllClientTable();
    }
}