<?php

namespace App\Console\Commands;

use App\Models\Customer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

use Artisan;

class ClearDataExecute extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ClearDataExecute {key}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear Data';

    protected $items = [
        ['name' => 'loginlog', 'table' => 'logon', 'timeField' => 'post_time', 'timeFieldType' => 'timestamp', 'remove' => true, 'backupTable' => false, 'retentionDays' => 45],

        ['name' => 'livematch', 'table' => 'user_room', 'timeField' => 'op_time', 'remove' => true, 'backupTable' => false, 'retentionDays' => 7],
        ['name' => 'livematch', 'table' => 'user_room_ext', 'timeField' => 'op_time', 'remove' => true, 'backupTable' => false, 'retentionDays' => 7],
        ['name' => 'UserEnterExitRoomWinLose', 'table' => 'user_enter_out', 'timeField' => 'post_time', 'remove' => true, 'backupTable' => false, 'retentionDays' => 45],

        ['name' => 'gm_loginlog', 'table' => 'gm_login_log', 'timeField' => 'created', 'remove' => true, 'backupTable' => false, 'retentionDays' => 45],
        ['name' => 'gm_actionlog', 'table' => 'gm_action_log', 'timeField' => 'created', 'remove' => true, 'backupTable' => false, 'retentionDays' => 45],
        ['name' => 'analysis_loginlog', 'table' => 'analysis_login_log', 'timeField' => 'created', 'remove' => true, 'backupTable' => false, 'retentionDays' => 45],
        ['name' => 'analysis_actionlog', 'table' => 'analysis_action_log', 'timeField' => 'created', 'remove' => true, 'backupTable' => false, 'retentionDays' => 45],
        ['name' => 'merchant_loginlog', 'table' => 'merchant_login_log', 'timeField' => 'created', 'remove' => true, 'backupTable' => false, 'retentionDays' => 45],
        ['name' => 'merchant_actionlog', 'table' => 'merchant_action_log', 'timeField' => 'created', 'remove' => true, 'backupTable' => false, 'retentionDays' => 45],
        ['name' => 'server_request_log', 'table' => 'server_request_log', 'timeField' => 'created', 'remove' => true, 'backupTable' => false, 'retentionDays' => 30],
        ['name' => 'server_post_log', 'table' => 'server_post_sub_log', 'timeField' => 'created', 'remove' => true, 'backupTable' => false, 'retentionDays' => 30, 'calls' => 'runServerPostSubLog'],
        ['name' => 'server_post_log', 'table' => 'server_post_log', 'timeField' => 'created', 'remove' => true, 'backupTable' => false, 'retentionDays' => 30],

        ['name' => 'gold_robot', 'table' => 'gold_robot', 'timeField' => 'post_time', 'remove' => true, 'backupTable' => false, 'retentionDays' => 7],
        ['name' => 'data_report', 'table' => 'data_report', 'timeField' => 'count_date', 'timeFieldType' => 'date', 'remove' => true, 'backupTable' => false, 'retentionDays' => 400],
        ['name' => 'room_gold_day_statistics', 'table' => 'room_gold_day_statistics', 'timeField' => 'create_time', 'remove' => true, 'backupTable' => false, 'retentionDays' => 400],
        ['name' => 'web_log_analysis', 'table' => 'web_log_analysis', 'timeField' => 'count_date', 'timeFieldType' => 'date', 'remove' => true, 'backupTable' => false, 'retentionDays' => 35],

        // big table
        ['name' => 'api_history', 'table' => 'transfer_inout', 'timeField' => 'create_time', 'timeFieldType' => 'timestamp144', 'remove' => true, 'backupTable' => 'transfer_inout_backup', 'retentionDays' => 60],
        ['name' => 'transfer_inout_backup', 'table' => 'transfer_inout_backup', 'timeField' => 'create_time', 'timeFieldType' => 'timestamp144', 'remove' => true, 'backupTable' => false, 'retentionDays' => 180 + 60],

        // big table
        ['name' => 'goldlog', 'table' => 'gold', 'timeField' => 'post_time', 'timeFieldType' => 'timestamp144', 'remove' => true, 'backupTable' => 'gold_backup', 'retentionDays' => 4],
        ['name' => 'gold_backup', 'table' => 'gold_backup', 'timeField' => 'post_time', 'timeFieldType' => 'timestamp144', 'remove' => true, 'backupTable' => false, 'retentionDays' => 45 + 4],

        // big table
        ['name' => 'playlog', 'table' => 'versus', 'timeField' => 'post_time', 'timeFieldType' => 'timestamp48', 'remove' => true, 'backupTable' => false, 'retentionDays' => 4],
        ['name' => 'playlog', 'table' => 'versus_list', 'timeField' => 'create_time', 'timeFieldType' => 'timestamp144', 'remove' => true, 'backupTable' => false, 'retentionDays' => 4],
    ];

    protected $realRun = true;

    protected $message = '';

    protected $sqlMessage = false;

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $key = $this->argument('key');
        if ($this->realRun == false) {
            $this->sqlMessage = true;
        }

        $this->addDBItems();
        // print_r($this->items);
        // exit;
        foreach ($this->items as $item) {

            if ($key == 'all' || $key == $item['name']) {
                // echo "功能：{$item['name']} 表: {$item['table']} 保留天数: {$item['retentionDays']} 是否备份:" . ($item['backupTable'] ? '是' : '否') . PHP_EOL;
                // $message = "功能：{$item['name']} 表: {$item['table']} 保留天数: {$item['retentionDays']} " . PHP_EOL;
                // $message = "Module：{$item['name']} Table: {$item['table']} retentionDays: {$item['retentionDays']} " . PHP_EOL;
                // $this->message .= $message;
                if ($this->realRun) {
                    $start = microtime(true);
                    // $message =  "start task:" . $item['name'] . PHP_EOL;
                    // $this->message .= $message;
                } else {
                    $message = "Module：{$item['name']} Table: {$item['table']} retentionDays: {$item['retentionDays']} " . PHP_EOL;
                    $this->message .= $message;
                }

                if (isset($item['calls'])) {
                    $this->{$item['calls']}($item);
                } else {
                    $firstDate = $this->getFirstDate($item);
                    if ($firstDate !== null) {
                        $calDays = $this->calDays($item, $firstDate);
                        foreach ($calDays as $date) {
                            if (isset($item['call'])) {
                                $this->{$item['call']}($item, $date);
                            } else {
                                $this->backupTable($item, $date);
                                $this->remove($item, $date);
                            }
                            sleep(0.01);
                        }
                    }
                }

                if ($this->realRun) {
                    $cost = intval((microtime(true) - $start) * 1000);
                    // $message = "over task:" . $item['name'] . ' cost_time(ms): ' . $cost . PHP_EOL;
                    $type = $item['timeFieldType'] ?? 'def';
                    if ($cost == 0) {
                        continue;
                    }
                    $message = "M：{$item['name']} T: {$item['table']} RD: {$item['retentionDays']} TFT: {$type} CT(ms): {$cost}" . PHP_EOL;
                    $this->message .= $message;
                }
                sleep(0.01);
            }
        }

        $message = 'Finish' . PHP_EOL;
        $this->message .= $message;

        if ($key == 'all') {
            $hostname = gethostname();
            $delay = strtotime(date('Y-m-d 10:00:00')) - time();
            $delay = $delay < 0 ? 0 : $delay;
            Artisan::queue('SendMessage', [
                'text' => "[" . date('Y-m-d H:i:s') . "]" . '[' . $hostname . '] Clear Data' . PHP_EOL. $this->message
            ])->onConnection('redis')->onQueue('default')->delay($delay);
        }

        echo $this->message;
        return 0;
    }

    protected function getFirstDate(array $item): mixed
    {
        $db = DB::reconnect('Master');
        $sql = "select `{$item['timeField']}` from `{$item['table']}` order by `{$item['timeField']}` asc limit 1";
        $res = $db->select($sql);
        if (empty($res)) {
            $v = null;
        } else {
            $v = $res[0]->{$item['timeField']};
            if (isset($item['timeFieldType']) && $item['timeFieldType'] == 'timestamp') {
                $v = date('Y-m-d', $v);
            }
        }
        return $v;
    }

    protected function calDays(array $item, string $firstDate): array
    {
        $startDate = date('Y-m-d', strtotime($firstDate));
        $endDate = date('Y-m-d', strtotime("-{$item['retentionDays']} days"));

        $startTime = strtotime($startDate);
        $endTime = strtotime($endDate);
        if ($endTime <= $startTime) {
            return [];
        }
        // echo $startDate . PHP_EOL;
        // echo $endDate . PHP_EOL;
        // echo PHP_EOL;
        $res = [];
        $days = ceil(($endTime - $startTime) / 86400);
        for ($i = 0; $i <= $days; $i++) {
            $res[] = date('Y-m-d', $startTime + $i * 86400);
        }

        return $res;
    }

    protected function backupTable(array $item, string $date): void
    {
        if ($item['backupTable'] === false) return;
        $db = DB::reconnect('Master');
        if (isset($item['timeFieldType']) && $item['timeFieldType'] == 'timestamp') {
            $start = strtotime($date);
            $end = strtotime($date) + 86400 - 1;
            $sql = "insert into `{$item['backupTable']}` select * from `{$item['table']}` where `{$item['timeField']}` between {$start} and {$end}";
            if ($this->sqlMessage) {
                $message = $sql . PHP_EOL;
                $this->message .= $message;
            }
            $this->realRun && $db->insert($sql);
        } else if (isset($item['timeFieldType']) && $item['timeFieldType'] == 'date') {
            $sql = "insert into `{$item['backupTable']}` select * from `{$item['table']}` where `{$item['timeField']}` = '{$date}'";
            if ($this->sqlMessage) {
                $message = $sql . PHP_EOL;
                $this->message .= $message;
            }
            $this->realRun && $db->insert($sql);
        } else if (isset($item['timeFieldType']) && $item['timeFieldType'] == 'timestamp24') {
            for ($i = 0; $i < 24; $i++) {
                $si = $i < 10 ? '0' . $i : $i;
                $sql = "insert into `{$item['backupTable']}` select * from `{$item['table']}` where `{$item['timeField']}` between '{$date} {$si}:00:00' and '{$date} {$si}:59:59'";
                if ($this->sqlMessage) {
                    $message = $sql . PHP_EOL;
                    $this->message .= $message;
                }
                $this->realRun && $db->insert($sql);
            }
        } else if (isset($item['timeFieldType']) && $item['timeFieldType'] == 'timestamp48') {
            for ($i = 0; $i < 24; $i++) {
                $si = $i < 10 ? '0' . $i : $i;
                $sql = "insert into `{$item['backupTable']}` select * from `{$item['table']}` where `{$item['timeField']}` between '{$date} {$si}:00:00' and '{$date} {$si}:29:59'";
                if ($this->sqlMessage) {
                    $message = $sql . PHP_EOL;
                    $this->message .= $message;
                }
                $this->realRun && $db->insert($sql);

                $sql = "insert into `{$item['backupTable']}` select * from `{$item['table']}` where `{$item['timeField']}` between '{$date} {$si}:30:00' and '{$date} {$si}:59:59'";
                if ($this->sqlMessage) {
                    $message = $sql . PHP_EOL;
                    $this->message .= $message;
                }
                $this->realRun && $db->insert($sql);
            }
        } else if (isset($item['timeFieldType']) && $item['timeFieldType'] == 'timestamp144') {
            for ($i = 0; $i < 24; $i++) {
                for ($j = 0; $j < 6; $j++) {
                    $si = $i < 10 ? '0' . $i : $i;
                    $start = date('i:s', 10 * 60 * $j);
                    $end = date('i:s', 10 * 60 * ($j + 1) - 1);
                    $sql = "insert into `{$item['backupTable']}` select * from `{$item['table']}` where `{$item['timeField']}` between '{$date} {$si}:{$start}' and '{$date} {$si}:{$end}'";
                    if ($this->sqlMessage) {
                        if ($i == 0 && $j == 0) {
                            $message = $sql . PHP_EOL;
                        } else if ($i == 23 && $j == 5) {
                            $message = PHP_EOL . $sql . PHP_EOL;
                        } else {
                            $message = '.';
                        }
                        $this->message .= $message;
                    }
                    $this->realRun && $db->insert($sql);
                }
            }
        } else {
            $sql = "insert into `{$item['backupTable']}` select * from `{$item['table']}` where `{$item['timeField']}` between '{$date} 00:00:00' and '{$date} 23:59:59'";
            if ($this->sqlMessage) {
                $message = $sql . PHP_EOL;
                $this->message .= $message;
            }
            $this->realRun && $db->insert($sql);
        }
    }

    protected function remove(array $item, string $date): void
    {
        if ($item['remove'] === false) return;
        $db = DB::reconnect('Master');
        if (isset($item['timeFieldType']) && $item['timeFieldType'] == 'timestamp') {
            $start = strtotime($date);
            $end = strtotime($date) + 86400 - 1;
            $sql = "delete from `{$item['table']}` where `{$item['timeField']}` <= '{$end}'";
            if ($this->sqlMessage) {
                $message = $sql . PHP_EOL;
                $this->message .= $message;
            }
            $this->realRun && $db->delete($sql);
        } else if (isset($item['timeFieldType']) && $item['timeFieldType'] == 'date') {
            $sql = "delete from `{$item['table']}` where `{$item['timeField']}` <= '{$date}'";
            if ($this->sqlMessage) {
                $message = $sql . PHP_EOL;
                $this->message .= $message;
            }
            $this->realRun && $db->delete($sql);
        } else if (isset($item['timeFieldType']) && $item['timeFieldType'] == 'timestamp24') {
            for ($i = 0; $i < 24; $i++) {
                $si = $i < 10 ? '0' . $i : $i;
                $sql = "delete from `{$item['table']}` where `{$item['timeField']}` <= '{$date} {$si}:59:59'";
                if ($this->sqlMessage) {
                    $message = $sql . PHP_EOL;
                    $this->message .= $message;
                }
                $this->realRun && $db->delete($sql);
            }
        } else if (isset($item['timeFieldType']) && $item['timeFieldType'] == 'timestamp48') {
            for ($i = 0; $i < 24; $i++) {
                $si = $i < 10 ? '0' . $i : $i;
                $sql = "delete from `{$item['table']}` where `{$item['timeField']}` <= '{$date} {$si}:29:59'";
                if ($this->sqlMessage) {
                    $message = $sql . PHP_EOL;
                    $this->message .= $message;
                }
                $this->realRun && $db->delete($sql);

                $sql = "delete from `{$item['table']}` where `{$item['timeField']}` <= '{$date} {$si}:59:59'";
                if ($this->sqlMessage) {
                    $message = $sql . PHP_EOL;
                    $this->message .= $message;
                }
                $this->realRun && $db->delete($sql);
            }
        } else if (isset($item['timeFieldType']) && $item['timeFieldType'] == 'timestamp144') {
            for ($i = 0; $i < 24; $i++) {
                for ($j = 0; $j < 6; $j++) {
                    $si = $i < 10 ? '0' . $i : $i;
                    $start = date('i:s', 10 * 60 * $j);
                    $end = date('i:s', 10 * 60 * ($j + 1) - 1);
                    $sql = "delete from `{$item['table']}` where `{$item['timeField']}` <= '{$date} {$si}:{$end}'";
                    if ($this->sqlMessage) {
                        if ($i == 0 && $j == 0) {
                            $message = $sql . PHP_EOL;
                        } else if ($i == 23 && $j == 5) {
                            $message = PHP_EOL . $sql . PHP_EOL;
                        } else {
                            $message = '.';
                        }
                        $this->message .= $message;
                    }
                    $this->realRun && $db->delete($sql);
                }
            }
        } else {
            $sql = "delete from `{$item['table']}` where `{$item['timeField']}` <= '{$date} 23:59:59'";
            if ($this->sqlMessage) {
                $message = $sql . PHP_EOL;
                $this->message .= $message;
            }
            $this->realRun && $db->delete($sql);
        }
    }

    protected function runServerPostSubLog(array $item): void
    {
        $db = DB::reconnect('Master');
        $serverPostTable = str_replace('server_post_sub_log', 'server_post_log', $item['table']);
        $sql = "select min(id) as id from `$serverPostTable`";
        $res = $db->select($sql);
        $id = empty($res) ? 0 : $res[0]->id;
        $sql = "delete from `{$item['table']}` where `pid` < '{$id}'";
        $this->realRun && $db->delete($sql);
    }

    protected function addDBItems()
    {
        $customer = new Customer();
        $c = $customer->get();
        $apiHistory = [];
        $transferInoutBackup = [];

        $serverRequestLog = [];
        $serverPostLog = [];
        $serverPostSubLog = [];

        foreach ($this->items as $k => $item) {
            if ($item['table'] == 'transfer_inout') {
                $apiHistory = $item;
                unset($this->items[$k]);
            }

            if ($item['table'] == 'transfer_inout_backup') {
                $transferInoutBackup = $item;
                unset($this->items[$k]);
            }

            if ($item['table'] == 'server_request_log') {
                $serverRequestLog = $item;
                unset($this->items[$k]);
            }

            if ($item['table'] == 'server_post_sub_log') {
                $serverPostSubLog = $item;
                unset($this->items[$k]);
            }

            if ($item['table'] == 'server_post_log') {
                $serverPostLog = $item;
                unset($this->items[$k]);
            }
        }

        foreach ($c as $k => $v) {
            $apiHistory['table'] = 'transfer_inout_' . $v['id'];
            $apiHistory['backupTable'] = 'transfer_inout_backup_' . $v['id'];

            $transferInoutBackup['table'] = 'transfer_inout_backup_' . $v['id'];

            $serverRequestLog['table'] = 'server_request_log_' . $v['id'];
            $serverPostLog['table'] = 'server_post_log_' . $v['id'];
            $serverPostSubLog['table'] = 'server_post_sub_log_' . $v['id'];

            $this->items[] = $apiHistory;
            $this->items[] = $transferInoutBackup;

            $this->items[] = $serverRequestLog;
            $this->items[] = $serverPostLog;
            $this->items[] = $serverPostSubLog;
        }
    }
}
