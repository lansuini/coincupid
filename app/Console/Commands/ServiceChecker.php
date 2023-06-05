<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\TransferInOut;
use App\Http\Library\MerchantCF;
use App\Models\Customer;
use App\Models\Manager\LoginLog as GMLoginLog;
use App\Models\Manager\Analysis\LoginLog as AnalysisLoginLog;
use App\Models\Manager\Merchant\LoginLog as MerchantLoginLog;
use App\Models\Manager\ActionLog as GMActionLog;
use App\Models\Manager\Analysis\ActionLog as AnalysisActionLog;
use App\Models\Manager\Merchant\ActionLog as MerchantActionLog;
use App\Models\ServerPostLog;
use App\Models\ServerRequestLog;
use Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableCell;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Output\BufferedOutput;

class ServiceChecker extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ServiceChecker {type}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'ServiceChecker';

    protected $start;

    protected $testing = false;

    protected $allowNotifyHour = [10, 11, 16, 17, 19, 20, 21, 22, 23];

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $type = $this->argument('type');
        if ($type == 'minute') {
            $this->minute();
        }
    }

    protected function minute()
    {
        // $this->checkServiceLogByYesterDay();
        // $this->checkServiceLogByMinute();
        // $this->checkApplicationLogByMinute();
        // exit;
        // $allowNotifyHour = [10, 11, 16, 17, 19, 20, 21, 22, 23];

        $h = date('H');
        $i = date('i');

        if ($this->testing != true && !in_array($h, $this->allowNotifyHour)) {
            echo 'Not Allow Notify' . PHP_EOL;
            return;
        }

        if ($i % 5 == 0) {
            $this->checkServiceLogByMinute();
        }

        if ($h == 10 && $i == 0 && false) {
            $this->checkServiceLogByYesterDay();
        }

        if ($i % 5 == 0) {
            $this->checkApplicationLogByMinute();
        }
        // $this->checkApplicationLogByMinute();
    }



    protected function checkApplicationLogByMinute()
    {
        $times = $this->getTime(__FUNCTION__);
        $start = $times['start'];
        $end = $times['end'];
        $this->start = microtime(true);
        // $start = date('Y-m-d H:i:s', time() - 5 * 60);
        // $end = date('Y-m-d H:i:s');
        $gmLLC = GMLoginLog::where('created', '>=', $start)->where('created', '<', $end)->count();
        $gmFLLC = GMLoginLog::where('created', '>=', $start)->where('created', '<', $end)->where('is_success', 0)->count();
        $gmALC = GMActionLog::where('created', '>=', $start)->where('created', '<', $end)->count();

        $analysisLLC = AnalysisLoginLog::where('created', '>=', $start)->where('created', '<', $end)->count();
        $analysisFLLC = AnalysisLoginLog::where('created', '>=', $start)->where('created', '<', $end)->where('is_success', 0)->count();
        $analysisALC = AnalysisActionLog::where('created', '>=', $start)->where('created', '<', $end)->count();

        $merchantLLC = MerchantLoginLog::where('created', '>=', $start)->where('created', '<', $end)->count();
        $merchantFLLC = MerchantLoginLog::where('created', '>=', $start)->where('created', '<', $end)->where('is_success', 0)->count();
        $merchantALC = MerchantActionLog::where('created', '>=', $start)->where('created', '<', $end)->count();

        $data = [[
            $gmLLC,
            $gmFLLC,
            $gmALC,

            $analysisLLC,
            $analysisFLLC,
            $analysisALC,

            $merchantLLC,
            $merchantFLLC,
            $merchantALC,
        ]];
        $ccc = 0;
        foreach ($data[0] as $k => $v) {
            if (!in_array($k, [1, 4, 7])) {
                continue;
            }

            if ($v > 0) {
                $ccc++;
            }
        }

        if ($ccc > 0 || $this->testing) {
            // $headers = ['LoginLogCnt', 'LoginFailCnt', 'ActionLogCnt'];

            if (true) {
                $message = 'Every 5 Minute Application Log Checker' . PHP_EOL;
                $message .= "[GM] LoginLogCnt: {$gmLLC} LoginFailCnt: {$gmFLLC} ActionLogCnt: {$gmALC}" . PHP_EOL;
                $message .= "[Analysis] LoginLogCnt: {$analysisLLC} LoginFailCnt: {$analysisFLLC} ActionLogCnt: {$analysisALC}" . PHP_EOL;
                $message .= "[Merchant] LoginLogCnt: {$merchantLLC} LoginFailCnt: {$merchantFLLC} ActionLogCnt: {$merchantALC}" . PHP_EOL;
                $message .= $start . " - " . $end . PHP_EOL;
            } else {

                $headers = ['LLC', 'LFC', 'ALC'];
                $output = new BufferedOutput();
                $table = new Table($output);
                $table->setHeaderTitle('Every 5 Minute Application Log Checker')
                    ->setFooterTitle($start . " - " . $end);
                $table->setHeaders([
                    [
                        new TableCell('GM', ['colspan' => count($headers)]),
                        new TableCell('Analysis', ['colspan' => count($headers)]),
                        new TableCell('Merchant', ['colspan' => count($headers)]),
                    ],
                    array_merge($headers, $headers, $headers)
                ])
                    ->setRows($data);
                $table->render();
                $message = $output->fetch();
            }
            $this->sendMessage($message);
            // echo 'send checkApplicationLogByMinute' . PHP_EOL;
        }
    }

    protected function checkServiceLogByMinute()
    {
        // $message = '';
        $times = $this->getTime(__FUNCTION__);
        $start = $times['start'];
        $end = $times['end'];
        $minute = $times['minute'];
        $c = Customer::get();
        foreach ($c as $v) {
            $this->start = microtime(true);
            // $start = date('Y-m-d H:i:s', time() - 5 * 60);
            // $end = date('Y-m-d H:i:s');
            $serverPostLog = new ServerPostLog();
            $serverPostLog = $serverPostLog->setTable('server_post_log_' . $v->id);
            $cnt1 = $serverPostLog->where('created', '>=', $start)
                ->where('created', '<', $end)
                ->count();

            $serverPostLog = new ServerPostLog();
            $serverPostLog = $serverPostLog->setTable('server_post_log_' . $v->id);
            $slowQueryCnt1 = $serverPostLog->where('created', '>=', $start)
                ->where('created', '<', $end)
                ->where('cost_time', '>=', '200')
                ->count();

            $serverPostLog = new ServerPostLog();
            $serverPostLog = $serverPostLog->setTable('server_post_log_' . $v->id);
            $errorQueryCnt1 = $serverPostLog->where('created', '>=', $start)
                ->where('created', '<', $end)
                ->whereNotNull('error_code')
                ->count();

            $serverRequestLog = new ServerRequestLog();
            $serverRequestLog = $serverRequestLog->setTable('server_request_log_' . $v->id);
            $cnt2 = $serverRequestLog->where('created', '>=', $start)
                ->where('created', '<', $end)
                ->count();

            $serverRequestLog = new ServerRequestLog();
            $serverRequestLog = $serverRequestLog->setTable('server_request_log_' . $v->id);
            $slowQueryCnt2 = $serverRequestLog->where('created', '>=', $start)
                ->where('created', '<', $end)
                ->where('cost_time', '>=', '500')
                ->count();

            $serverRequestLog = new serverRequestLog();
            $serverRequestLog = $serverRequestLog->setTable('server_request_log_' . $v->id);
            $errorQueryCnt2 = $serverRequestLog->where('created', '>=', $start)
                ->where('created', '<', $end)
                ->where('is_success', 0)
                ->count();

            $flow1 = round($cnt1 / $minute, 2);
            $flow2 = round($cnt2 / $minute, 2);

            if (
                $slowQueryCnt1 >= 5 || $slowQueryCnt2 >= 5 ||
                $errorQueryCnt1 >= 100 || $errorQueryCnt2 >= 1 ||
                $flow1 >= 200 || $flow2 >= 200 ||
                $this->testing
            ) {
                if (true) {
                    $message = '';
                    $message .= "Every 5 Minute Service Checker:" . $v->company_name . PHP_EOL;
                    $message .= "PostLog Cnt: {$cnt1} SlowCnt(>=200ms): {$slowQueryCnt1} ErrorCnt: {$errorQueryCnt1} Flow: {$flow1}/min" . PHP_EOL;
                    $message .= "RequestLog Cnt: {$cnt2} SlowCnt(>=500ms): {$slowQueryCnt2} ErrorCnt: {$errorQueryCnt2} Flow: {$flow2}/min" . PHP_EOL;
                    $message .= $start . " - " . $end . PHP_EOL;
                } else {
                    $data = [
                        [$cnt1, $slowQueryCnt1, $errorQueryCnt1, $cnt2, $slowQueryCnt2, $errorQueryCnt2]
                    ];
                    $headers = ['cnt', 'slowCnt', 'errorCnt'];
                    $output1 = new BufferedOutput();
                    $table = new Table($output1);
                    $table->setHeaderTitle('Every 5 Minute Checker:' . $v->company_name)
                        ->setFooterTitle($start . " - " . $end);
                    $table->setHeaders([
                        [new TableCell('PostLog', ['colspan' => count($headers)]), new TableCell('RequestLog', ['colspan' => count($headers)])],
                        array_merge($headers, $headers)
                    ])
                        ->setRows($data);
                    $table->render();
                    $message = $output1->fetch();
                }
                $this->sendMessage($message);
            }
        }
        // return $message;
    }

    protected function checkServiceLogByYesterDay()
    {
        function getSym1($n1, $n2)
        {
            return $n1 == $n2 ? '(0)' : ($n1 > $n2 ? '(+' . round($n1 - $n2, 2) . ')' : '(-' . round($n2 - $n1, 2) . ')');
        }

        $serverPostLogGet = function ($clientId, $date) {
            $sql = "select z.h, (IF(a.c is null, 0, a.c)  + IF(b.c is null, 0, b.c) ) as cnt, IF(a.c is null, 0, a.c) as succ_cnt from 
            (
                select 0 as h union
                select 1 as h union
                select 2 as h union
                select 3 as h union
                select 4 as h union
                select 5 as h union
                select 6 as h union
                select 7 as h union
                select 8 as h union
                select 9 as h union
                select 10 as h union
                select 11 as h union
                select 12 as h union
                select 13 as h union
                select 14 as h union
                select 15 as h union
                select 16 as h union
                select 17 as h union
                select 18 as h union
                select 19 as h union
                select 20 as h union
                select 21 as h union
                select 22 as h union
                select 23 as h
            ) z left join
            (
                select hour(created) as h, count(*) as c from server_post_log_{$clientId}
                where created >= '{$date} 00:00:00' and  created <= '{$date} 23:59:59' 
                and error_code is null
                group by hour(created)
            ) a on z.h = a.h left join
            (
                select hour(created) as h, count(*) as c from server_post_log_{$clientId}
                where created >= '{$date} 00:00:00' and  created <= '{$date} 23:59:59' 
                and error_code is not null
                group by hour(created)
            ) b on z.h = b.h";

            $data = DB::connection('Master')->select($sql);
            return $data;
        };

        $serverRequestLogGet = function ($clientId, $date) {
            $sql = "select z.h, (IF(a.c is null, 0, a.c)  + IF(b.c is null, 0, b.c) ) as cnt, IF(a.c is null, 0, a.c) as succ_cnt from 
            (
                select 0 as h union
                select 1 as h union
                select 2 as h union
                select 3 as h union
                select 4 as h union
                select 5 as h union
                select 6 as h union
                select 7 as h union
                select 8 as h union
                select 9 as h union
                select 10 as h union
                select 11 as h union
                select 12 as h union
                select 13 as h union
                select 14 as h union
                select 15 as h union
                select 16 as h union
                select 17 as h union
                select 18 as h union
                select 19 as h union
                select 20 as h union
                select 21 as h union
                select 22 as h union
                select 23 as h
            ) z left join
            (
                select hour(created) as h, count(*) as c from server_request_log_{$clientId}
                where created >= '{$date} 00:00:00' and  created <= '{$date} 23:59:59' 
                and is_success = 1
                group by hour(created)
            ) a on z.h = a.h left join
            (
                select hour(created) as h, count(*) as c from server_request_log_{$clientId}
                where created >= '{$date} 00:00:00' and  created <= '{$date} 23:59:59' 
                and is_success = 0
                group by hour(created)
            ) b on z.h = b.h";
            $data = DB::connection('Master')->select($sql);
            return $data;
        };

        // $message = "Day Report" . PHP_EOL;
        $data = [];
        $c = Customer::get();
        for ($i = 0; $i < 24; $i++) {
            $data[$i] = [0, 0, 0, 0, 0, 0, 0];
        }
        foreach ($c as $v) {
            $this->start = microtime(true);
            $ccc = 0;
            foreach ([time() - 86400, time() - 2 * 86400, time() - 8 * 86400] as $k => $time) {
                $d = date('Y-m-d', $time);
                $temp1 = $serverPostLogGet($v->id, $d);
                $temp2 = $serverRequestLogGet($v->id, $d);
                $key = $k + 1;
                $key2 = $k + 3 + 1;
                foreach ($temp1 as $t) {
                    $t = (array) $t;
                    $data[$t['h']][0] = $t['h'];
                    $data[$t['h']][$key] = $t['cnt'] . '/' . $t['succ_cnt'];
                    if ($t['cnt'] > 0 || $t['succ_cnt'] > 0) {
                        $ccc++;
                    }
                }

                foreach ($temp2 as $t) {
                    $t = (array) $t;
                    $data[$t['h']][$key2] = $t['cnt'] . '/' . $t['succ_cnt'];
                    if ($t['cnt'] > 0 || $t['succ_cnt'] > 0) {
                        $ccc++;
                    }
                }
            }

            $data2 = [];
            if ($ccc > 0) {

                foreach ($data as $nk => $nv) {
                    $nv1 = explode('/', $nv[1]);
                    $nv2 = explode('/', $nv[2]);
                    $nv3 = explode('/', $nv[3]);
                    $sv = [
                        $nv1[0] > 0 ? round(($nv1[1] / $nv1[0]) * 100, 2) : 0,
                        $nv2[0] > 0 ? round(($nv2[1] / $nv2[0]) * 100, 2) : 0,
                        $nv3[0] > 0 ? round(($nv3[1] / $nv3[0]) * 100, 2) : 0
                    ];
                    $t1 = $nv1[0] . '/' . $nv2[0] . getSym1($nv1[0], $nv2[0]) . '/' . $nv3[0] . getSym1($nv1[0], $nv2[0]);
                    $t2 = $nv1[1] . '/' . $nv2[1] . getSym1($nv1[1], $nv2[1]) . '/' . $nv3[1] . getSym1($nv1[1], $nv2[1]);
                    $t3 = $sv[0] . '/' . $sv[1] . getSym1($sv[0], $sv[1]) . '/' . $sv[2] . getSym1($sv[0], $sv[2]);
                    // $data[$nk] = $nv;

                    $step = 3;
                    $nv1 = explode('/', $nv[1 + $step]);
                    $nv2 = explode('/', $nv[2 + $step]);
                    $nv3 = explode('/', $nv[3 + $step]);
                    $sv = [
                        $nv1[0] > 0 ? round(($nv1[1] / $nv1[0]) * 100, 2) : 0,
                        $nv2[0] > 0 ? round(($nv2[1] / $nv2[0]) * 100, 2) : 0,
                        $nv3[0] > 0 ? round(($nv3[1] / $nv3[0]) * 100, 2) : 0
                    ];
                    $rt1 = $nv1[0] . '/' . $nv2[0] . getSym1($nv1[0], $nv2[0]) . '/' . $nv3[0] . getSym1($nv1[0], $nv2[0]);
                    $rt2 = $nv1[1] . '/' . $nv2[1] . getSym1($nv1[1], $nv2[1]) . '/' . $nv3[1] . getSym1($nv1[1], $nv2[1]);
                    $rt3 = $sv[0] . '/' . $sv[1] . getSym1($sv[0], $sv[1]) . '/' . $sv[2] . getSym1($sv[0], $sv[2]);

                    $data2[] = [
                        $nv[0],
                        $t1,
                        $t2,
                        $t3,

                        $rt1,
                        $rt2,
                        $rt3,
                    ];
                }

                // $data2[] = ['-', ]
            }

            if ($ccc > 0) {
                // $headers = ['-1d', '-2d', '-8d', 'TotalCompare', 'SuccCompare', 'SuccRateCompare'];
                $headers = ['Total', 'Succ', 'SuccRate'];
                $output1 = new BufferedOutput();
                $table = new Table($output1);
                $table->setHeaderTitle('Company:' . $v->company_name . '(-1days / -2days / -8days Compare)');
                $table->setHeaders([
                    ['', new TableCell('PostLog', ['colspan' => count($headers)]), new TableCell('RequestLog', ['colspan' => count($headers)])],
                    array_merge(['H'], $headers, $headers)
                ])
                    ->setRows($data2);
                $table->render();
                $message = $output1->fetch();
                $this->sendMessage($message);
            }
        }
    }

    protected function getTime($functionName)
    {
        $redis = Redis::connection('cache');
        $data = $redis->get('ServiceChecker:' . $functionName);
        $now = date('Y-m-d H:i:s');
        $data = !empty($data) ? json_decode($data, true) :
            [
                'start' => date('Y-m-d 00:00:00'),
                'end' => $now,
            ];

        if ($data['end'] != $now) {
            $data['start'] = $data['end'];
            $data['end'] = $now;
        }

        $redis->setex('ServiceChecker:' . $functionName, 86400, json_encode($data));
        $data['minute'] = round((strtotime($data['end']) - strtotime($data['start'])) / 60, 5);
        return $data;
    }

    protected function sendMessage($message)
    {
        $d = date('Y-m-d H:i:s');
        $hostname = gethostname();
        $header = "Time: {$d} [{$hostname}] Checker:" . PHP_EOL;
        $cost = intval((microtime(true) - $this->start) * 1000);
        $end = PHP_EOL . ' cost_time(ms): ' . $cost . PHP_EOL;
        if ($this->testing != true) {
            Artisan::queue('SendMessage', [
                'text' => $header . $message . $end
            ])->onConnection('redis')->onQueue('default');
        } else {
            echo $header . $message . $end . PHP_EOL;
        }
    }
}
