<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Http\Library\MerchantCB;
use App\Models\ServerRequestLog;
use App\Models\WebLogAnalysis as ModelWebLogAnalysis;
use Illuminate\Support\Facades\Log;
use Artisan;
use SplFileObject;



/**
    $format = '"$remote_addr" $request_time - $remote_user d [$time_local] "$request" $status $body_bytes_sent "$http_referer" "$http_user_agent" "$http_x_forwarded_for"';

     log_format  main  '$remote_addr - $remote_user [$time_local] "$request" '
                      '$status $body_bytes_sent "$http_referer" '
                      '"$http_user_agent" "$http_x_forwarded_for" "$request_body"';

                      
     log_format  main  '$remote_addr - $remote_user [$time_local] $http_host "$request" '
    '$status $body_bytes_sent "$http_referer" '
    '"$http_user_agent" upstream_status: $upstream_status upstream_response_time: $upstream_response_time'
    '"http_x_forwarded_for":"$http_x_forwarded_for" '
    '$request_time $upstream_response_time '
    '"body": $request_body'
    '"client-ip": "$http_client_ip"'
    '"http_x_real_ip": "$http_x_real_ip"';

    php artisan WebLogAnalysis nginx /Users/beck/Downloads/nginx/20221221/20221221_h5-access.log
    php artisan WebLogAnalysis nginxerror /Users/beck/Downloads/nginx/20221221/20221221_h5-error.log
    php artisan WebLogAnalysis php /Users/beck/Downloads/php/error.log
    php artisan WebLogAnalysis laravel /Users/beck/mycode/php/project1/pd/storage/logs/laravel-2022-12-22.log
 */
class WebLogAnalysis extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = ' WebLogAnalysis {type} {path}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'WebLogAnalysis type: php\nginx\nginxerror\crontab';

    protected $testing = false;

    protected $allowNotifyHour = [10, 11, 16, 17, 19, 20, 21, 22, 23];
    
    protected function getCrontabListenFilePath()
    {
        if ($this->testing) {
            return [
                'php' => [['/Users/beck/Downloads/php/error.log', 'php']],
                'nginx' => [
                    ['/Users/beck/Downloads/nginx/20221221/20221221_h5-access.log', 'H5'],
                    ['/Users/beck/Downloads/nginx/20221221/20221221_gm-access.log', 'GM'],
                    ['/Users/beck/Downloads/nginx/20221221/20221221_mechant-access.log', 'Merchant'],
                    ['/Users/beck/Downloads/nginx/20221221/20221221_server-access.log', 'Server'],
                    ['/Users/beck/Downloads/nginx/20221221/20221221_analysis-access.log', 'Analysis'],
                ],
                'nginxerror' => [
                    ['/Users/beck/Downloads/nginx/20221221/20221221_h5-error.log', 'H5'],
                    ['/Users/beck/Downloads/nginx/20221221/20221221_gm-error.log', 'GM'],
                    ['/Users/beck/Downloads/nginx/20221221/20221221_mechant-error.log', 'Merchant'],
                    ['/Users/beck/Downloads/nginx/20221221/20221221_server-error.log', 'Server'],
                    ['/Users/beck/Downloads/nginx/20221221/20221221_analysis-error.log', 'Analysis'],
                ],
                'laravel' => [['/Users/beck/mycode/php/project1/pd/storage/logs/laravel-2022-12-22.log', 'laravel']],
            ];
        }
        return [
            'php' => [['/data/logs/php/error.log', 'php']],
            'nginx' => [
                ['/data/logs/nginx/h5-access.log', 'H5'],
                ['/data/logs/nginx/gm-access.log', 'GM'],
                ['/data/logs/nginx/mechant-access.log', 'Merchant'],
                ['/data/logs/nginx/server-access.log', 'Server'],
                ['/data/logs/nginx/analysis-access.log', 'Analysis'],
            ],
            'nginxerror' => [
                ['/data/logs/nginx/h5-error.log', 'H5'],
                ['/data/logs/nginx/gm-error.log', 'GM'],
                ['/data/logs/nginx/mechant-error.log', 'Merchant'],
                ['/data/logs/nginx/server-error.log', 'Server'],
                ['/data/logs/nginx/analysis-error.log', 'Analysis'],
            ],
            'laravel' => [[base_path('storage/logs/laravel-' . date('Y-m-d') . '.log'), 'laravel']],

            // 'laravel' => [['/data/hofa/pd/storage/logs/laravel-' . date('Y-m-d') . '.log', 'laravel']],
        ];
    }
    protected function getNginxErrorFormats()
    {
        $datetime = function ($txt) {
            return \DateTime::createFromFormat("Y/m/d H:i:s", $txt);
        };

        $string = function ($txt) {
            return $txt;
        };

        return [
            'time_local' => ['pos' => function ($arrs) {
                return $arrs[0] . ' ' . $arrs[1];
            }, 'type' => $datetime],
            'error' => ['pos' => function ($arrs) {
                return implode(' ', array_slice($arrs, 2, count($arrs)));
            }, 'type' => $string]
        ];
    }

    protected function getNginxFormats()
    {
        $datetime = function ($txt) {
            return \DateTime::createFromFormat("d/M/Y:H:i:s O", $txt);
        };

        $url = function ($txt, $t2, $t3) {
            $txts = explode(' ', $txt);
            return ['method' => $txts[0] ?? '', 'url' => !empty($txts[1]) ? parse_url($txts[1]) : '', 'protocol' => $txts[2] ?? ''];
        };

        $integer = function ($txt) {
            return intval($txt);
        };

        $decimal = function ($txt) {
            return round(floatval($txt), 3);
        };

        $string = function ($txt) {
            return $txt;
        };

        return [
            'remote_addr' => ['pos' => 0, 'type' => $string],
            'remote_user' => ['pos' => 2, 'type' => $string],
            'time_local' => ['pos' => function ($arrs) {
                return $arrs[3] . ' ' . $arrs[4];
            }, 'type' => $datetime, 'quotationMarksRemove' => true],
            'http_host' => ['pos' => 5, 'type' => $string],
            'request' => ['pos' => 7, 'type' => $url, 'quotationMarksRemove' => true],
            'status' => ['pos' => 8, 'type' => $integer],
            'body_bytes_sent' => ['pos' => 9, 'type' => $integer],
            'http_referer' => ['pos' => 11, 'type' => $string, 'quotationMarksRemove' => true],
            'http_user_agent' => ['pos' => 13, 'type' => $string, 'quotationMarksRemove' => true],
            'upstream_status' => ['pos' => 15, 'type' => $integer],
            'upstream_response_time' => ['pos' => 17, 'type' => $decimal],
            'http_x_forwarded_for' => ['pos' => 19, 'type' => $string, 'quotationMarksRemove' => true],
            'request_time' => ['pos' => 20, 'type' => $decimal],
            'upstream_response_time' => ['pos' => 21, 'type' => $decimal],
            'body' => ['pos' => 24, 'type' => $string],
            'http_client_ip' => ['pos' => 27, 'type' => $string, 'quotationMarksRemove' => true],
            'http_x_real_ip' => ['pos' => 30, 'type' => $string, 'quotationMarksRemove' => true],
        ];
    }

    protected function getPHPFormats()
    {
        $datetime = function ($txt) {
            return \DateTime::createFromFormat("d-M-Y H:i:s", $txt);
        };
        $string = function ($txt) {
            return $txt;
        };
        return [
            'time_local' => [
                'pos' => function ($arrs) {
                    return $arrs[0] . ' ' . $arrs[1];
                }, 'type' => $datetime, 'quotationMarksRemove' => true
            ],
            'type' => ['pos' => function ($arrs) {
                return str_replace(':', '', $arrs[2]);
            }, 'type' => $string],
            'message' => ['pos' => function ($arrs) {
                return implode(' ', array_slice($arrs, 3, count($arrs)));
            }, 'type' => $string]
        ];
    }

    protected function getLaravelFormats()
    {
        $datetime = function ($txt) {
            return \DateTime::createFromFormat("Y-m-d H:i:s", $txt);
        };
        $string = function ($txt) {
            return $txt;
        };
        return [
            'time_local' => [
                'pos' => function ($arrs) {
                    return $arrs[0] . ' ' . $arrs[1];
                }, 'type' => $datetime, 'quotationMarksRemove' => true
            ],
            'type' => ['pos' => function ($arrs) {
                return str_replace([':', env('APP_ENV') . '.'], '', $arrs[2]);
            }, 'type' => $string],
            'message' => ['pos' => function ($arrs) {
                return implode(' ', array_slice($arrs, 3, count($arrs)));
            }, 'type' => $string]
        ];
    }

    public function getHourKey($keys, $datetime)
    {

        // $datetime->format('H') * 3600 . '|' . $datetime->format('i') * 60;
        $num = $datetime->format('H') * 3600 + $datetime->format('i') * 60;
        // print_r($keys);
        foreach ($keys as $k => $v) {
            if ($num < $v) {
                return $keys[$k - 1];
            }
        }
        return last($keys);
    }

    public function handle()
    {
        $type = $this->argument('type');
        $path = $this->argument('path');

        switch ($type) {
            case 'nginxerror':
                $res = $this->runAnalysisNginxErrorLog($path);
                break;
            case 'nginx':
                $res = $this->runAnalysisNginxLog($path);
                break;
            case 'php':
                $res = $this->runAnalysisPHPLog($path);
                break;
            case 'laravel':
                $res = $this->runAnalysisLaravelLog($path);
                break;
            case 'reports':
                $res = $this->reports($path);
                break;
            case 'cron':
                if ($path == 1) {
                    $countDate = date('Y-m-d');

                    $h = date('H');
                    if ($this->testing != true && !in_array($h, $this->allowNotifyHour)) {
                        echo 'Not Allow Notify' . PHP_EOL;
                        return;
                    }

                    $this->runCrontab($countDate);
                    $this->reports();
                } else {
                    $this->runCrontab($path);
                }
                $res = [];
                break;
        }

        print_r($res);
        echo 'Finish!' . PHP_EOL;
    }

    public function runCrontab($countDate)
    {
        $paths = $this->getCrontabListenFilePath();
        $data = ['nginxerror' => [], 'nginx' => [], 'php' => [], 'laravel' => []];
        // $countDate = date('Y-m-d');
        $hostname = gethostname();
        if ($this->testing) {
            $countDate = null;
        }
        $res = ModelWebLogAnalysis::where('count_date', $countDate)->where('host', $hostname)->first();
        $content = !empty($res) ? json_decode($res->content, true) : [];
        foreach ($paths as $n => $ps) {
            foreach ($ps as $p) {
                // $name = basename($p);
                if (!is_file($p[0])) {
                    echo "NO_FILE_PATH:" . $p[0] . PHP_EOL;
                    break;
                } else {
                    echo "Run:" . $p[0] . PHP_EOL;
                }

                switch ($n) {
                    case 'nginxerror':
                        $default = isset($content['nginxerror']) && isset($content['nginxerror'][$p[1]]) ? $content['nginxerror'][$p[1]] : null;
                        $data['nginxerror'][$p[1]] = $this->runAnalysisNginxErrorLog($p[0], $countDate, $default);
                        break;
                    case 'nginx':
                        $default = isset($content['nginx']) && isset($content['nginx'][$p[1]]) ? $content['nginx'][$p[1]] : null;
                        $data['nginx'][$p[1]] = $this->runAnalysisNginxLog($p[0], $countDate, $default);
                        break;
                    case 'php':
                        $default = isset($content['php']) && isset($content['php'][$p[1]]) ? $content['php'][$p[1]] : null;
                        $data['php'][$p[1]] = $this->runAnalysisPHPLog($p[0], $countDate, $default);
                        break;
                    case 'laravel':
                        $default = isset($content['laravel']) && isset($content['laravel'][$p[1]]) ? $content['laravel'][$p[1]] : null;
                        $data['laravel'][$p[1]] = $this->runAnalysisLaravelLog($p[0], $countDate, $default);
                        break;
                }
            }
        }

        if ($this->testing == false) {
            if (empty($res)) {
                ModelWebLogAnalysis::create([
                    'count_date' => $countDate,
                    'content' => json_encode($data),
                    'host' => $hostname,
                ]);
            } else {
                ModelWebLogAnalysis::where('count_date', $countDate)->where('host', $hostname)->update([
                    'content' => json_encode($data),
                ]);
            }
        } else {
            file_put_contents('/Users/beck/mycode/php/project1/pd/sb.json', json_encode($data));
        }
    }

    public function runAnalysisLaravelLog($path, $countDate = null)
    {
        $start = microtime(true);
        $struct1 = $this->createStruct();
        $this->fetchFileLine($path, 0, 0, function ($lineNum, $line) use (&$struct1, $countDate) {
            if (empty($line)) {
                return;
            }
            if (strpos($line, ' ' . env('APP_ENV') . '.')  === false) {
                return;
            }

            $vars = $this->parseNginxLog($line, $this->getLaravelFormats());

            if ($countDate && $vars['time_local']->format('Y-m-d') != $countDate) {
                return;
            }

            if (isset($vars['type'])) {
                $type = $vars['type'];
                $struct1['total'][$type] = isset($struct1['total'][$type]) ? $struct1['total'][$type] + 1 : 1;
                $hourField = $this->getHourKey(array_keys($struct1['hours']), $vars['time_local']);
                $struct1['hours'][$hourField][$type] = isset($struct1['hours'][$hourField][$type]) ? $struct1['hours'][$hourField][$type] + 1 : 1;
            }
        });

        $cost = intval((microtime(true) - $start) * 1000);
        return ['struct1' => $struct1, 'cost' => $cost];
    }

    public function runAnalysisPHPLog($path, $countDate = null)
    {
        $start = microtime(true);
        $struct1 = $this->createStruct();
        $this->fetchFileLine($path, 0, 0, function ($lineNum, $line) use (&$struct1, $countDate) {
            if (empty($line)) {
                return;
            }
            $vars = $this->parseNginxLog($line, $this->getPHPFormats());

            if ($countDate && $vars['time_local']->format('Y-m-d') != $countDate) {
                return;
            }

            if (isset($vars['type'])) {
                $type = $vars['type'];
                $struct1['total'][$type] = isset($struct1['total'][$type]) ? $struct1['total'][$type] + 1 : 1;
                $hourField = $this->getHourKey(array_keys($struct1['hours']), $vars['time_local']);
                $struct1['hours'][$hourField][$type] = isset($struct1['hours'][$hourField][$type]) ? $struct1['hours'][$hourField][$type] + 1 : 1;
            }
        });

        $cost = intval((microtime(true) - $start) * 1000);
        return ['struct1' => $struct1, 'cost' => $cost];
    }

    public function runAnalysisNginxErrorLog($path, $countDate = null)
    {
        $start = microtime(true);
        $struct1 = $this->createStruct2();
        $this->fetchFileLine($path, 0, 0, function ($lineNum, $line) use (&$struct1, $countDate) {
            if (empty($line)) {
                return;
            }

            $vars = $this->parseNginxLog($line, $this->getNginxErrorFormats());

            if ($countDate && $vars['time_local']->format('Y-m-d') != $countDate) {
                return;
            }

            $struct1['total'] = isset($struct1['total']) ? $struct1['total'] + 1 : 1;
            $hourField = $this->getHourKey(array_keys($struct1['hours']), $vars['time_local']);
            $struct1['hours'][$hourField] = isset($struct1['hours'][$hourField]) ? $struct1['hours'][$hourField] + 1 : 1;
        });

        $cost = intval((microtime(true) - $start) * 1000);
        return ['struct1' => $struct1, 'cost' => $cost];
    }

    public function createStruct()
    {
        $struct = [
            'total' => [],
            'hours' => [],
        ];
        for ($i = 0; $i < 86400; $i = $i + 10 * 60) {
            $struct['hours'][$i]  = [];
        }
        return $struct;
    }

    public function createStruct2()
    {
        $struct = [
            'total' => 0,
            'hours' => [],
        ];
        for ($i = 0; $i < 86400; $i = $i + 10 * 60) {
            $struct['hours'][$i]  = 0;
        }
        return $struct;
    }

    public function runAnalysisNginxLog($path, $countDate = null)
    {
        $start = microtime(true);
        // nginx - access log
        $struct1 = $this->createStruct();
        $struct2 = $this->createStruct();
        $struct3 = [];

        $this->fetchFileLine($path, 0, 0, function ($lineNum, $line) use (&$struct1, &$struct2, &$struct3, $countDate) {
            if (empty($line)) {
                return;
            }
            $vars = $this->parseNginxLog($line, $this->getNginxFormats());

            if ($countDate && $vars['time_local']->format('Y-m-d') != $countDate) {
                return;
            }

            $upstreamStatus = $vars['upstream_status'];
            $struct1['total'][$upstreamStatus] = isset($struct1['total'][$upstreamStatus]) ? $struct1['total'][$upstreamStatus] + 1 : 1;
            $hourField = $this->getHourKey(array_keys($struct1['hours']), $vars['time_local']);
            $struct1['hours'][$hourField][$upstreamStatus] = isset($struct1['hours'][$hourField][$upstreamStatus]) ? $struct1['hours'][$hourField][$upstreamStatus] + 1 : 1;

            $status = $vars['status'];
            $struct2['total'][$status] = isset($struct2['total'][$status]) ? $struct2['total'][$status] + 1 : 1;
            $hourField = $this->getHourKey(array_keys($struct2['hours']), $vars['time_local']);
            $struct2['hours'][$hourField][$status] = isset($struct2['hours'][$hourField][$status]) ? $struct2['hours'][$hourField][$status] + 1 : 1;

            if (isset($vars['request']['method']) && isset($vars['request']['url']) && isset($vars['request']['url']['path'])) {
                $path = $vars['request']['method'] . ':' . $vars['request']['url']['path'];
                if (!isset($struct3[$path])) {
                    $struct3[$path] = [
                        'm' => $vars['request']['method'],
                        'p' => $vars['request']['url']['path'],
                        'c' => 1,
                        'rt' => $vars['request_time'],
                        'rts' => $vars['request_time'],
                        'urt' => $vars['upstream_response_time'],
                        'urts' => $vars['upstream_response_time'],
                        'avg_rt' => $vars['request_time'],
                        'avg_urt' => $vars['upstream_response_time'],
                        'm_t_500_rt_c' => 0,
                        'm_t_500_urt_c' => 0,
                        'us' => [
                            $upstreamStatus => 1
                        ],
                        'ss' => [
                            $status => 1
                        ],
                    ];
                } else {
                    $struct3[$path]['c'] += 1;
                    $struct3[$path]['rt'] += $vars['request_time'];
                    $struct3[$path]['rts'] = $struct3[$path]['rts'] > $vars['request_time'] ? $struct3[$path]['rts'] : $vars['request_time'];
                    $struct3[$path]['urt'] += $vars['upstream_response_time'];
                    $struct3[$path]['urts'] = $struct3[$path]['urts'] > $vars['upstream_response_time'] ? $struct3[$path]['urts'] : $vars['upstream_response_time'];

                    $struct3[$path]['avg_rt'] = round($struct3[$path]['rt'] / $struct3[$path]['c'], 4);
                    $struct3[$path]['avg_urt'] = round($struct3[$path]['urt'] / $struct3[$path]['c'], 4);

                    if ($vars['request_time'] > 0.5) {
                        $struct3[$path]['m_t_500_rt_c']++;
                    }

                    if ($vars['upstream_response_time'] > 0.5) {
                        $struct3[$path]['m_t_500_urt_c']++;
                    }


                    $struct3[$path]['us'][$upstreamStatus] = isset($struct3[$path]['us'][$upstreamStatus]) ? $struct3[$path]['us'][$upstreamStatus] + 1 : 1;
                    $struct3[$path]['ss'][$status] = isset($struct3[$path]['ss'][$status]) ? $struct3[$path]['ss'][$status] + 1 : 1;
                }
            }
        });
        // print_r($struct3);
        // echo count($struct3) . PHP_EOL;
        $cost = intval((microtime(true) - $start) * 1000);
        // echo ' cost_time(ms): ' . $cost . PHP_EOL;
        // echo 'Finish!' . PHP_EOL;
        return ['struct1' => $struct1, 'struct2' => $struct2, 'struct3' => $struct3, 'cost' => $cost];
    }

    public function parseNginxLog($line, $formats)
    {
        $splitText = [];
        $fullText = '';
        $isHaveQuotationMarks = false;
        for ($i = 0; $i < strlen($line); $i++) {
            if ($line[$i] == '"') {
                $isHaveQuotationMarks = !$isHaveQuotationMarks;
            }

            if ($isHaveQuotationMarks == true && $line[$i] == '"') {
                $splitText[] = trim($fullText);
                $fullText = '';
            }

            if ($isHaveQuotationMarks == false && $line[$i] == ' ') {
                $splitText[] =  trim($fullText);
                $fullText = '';
            }

            $fullText .= substr($line, $i, 1);
        }
        $splitText[] = trim($fullText);
        $vars = [];
        foreach ($formats as $feild => $format) {
            $v = is_numeric($format['pos']) ? $splitText[$format['pos']] : $format['pos']($splitText);
            if (isset($format['quotationMarksRemove']) && $format['quotationMarksRemove']) {
                $v = substr($v, 1, strlen($v) - 2);
            }

            $v = $format['type']($v, $splitText, $line);
            $vars[$feild] = $v;
        }
        // print_r($vars);
        return $vars;
    }

    public function fetchFileLine($filePath, $start, $limit, $callable)
    {
        $r = 0;
        $file = new SplFileObject($filePath, "r");
        if ($start > 0) {
            $file->seek($start);
        }

        while (!$file->eof()) {
            $r++;
            $line  =  $file->fgets();

            // trim it, and then check if its empty
            if (empty(trim($line))) {
                // skips the current iteration
                continue;
            }

            // $this->parseNginxLog($line);
            $callable($r, $line);

            if ($limit != 0 && $r == $limit) {
                break;
            }
        }
    }

    protected function getNginxHourData($name, $data, $h)
    {
        $arr24 = [];
        for ($i = 0; $i < 24; $i++) {
            $arr24[$i] = [];
        }

        foreach ($data as $t => $v) {
            $key24 = intval($t / 3600);
            foreach ($v as $c => $n) {
                $arr24[$key24][$c] = isset($arr24[$key24][$c]) ? $arr24[$key24][$c] + $n : $n;
            }
        }

        $total = array_sum($arr24[$h]);
        $succ = 0;
        $codes = '';
        foreach ($arr24[$h] as $k => $v) {
            $codes .= " Code-{$k}: " . $v;
            if ($k == 200 || $k == 302) {
                $succ += $v;
            }
        }
        $rate = $total > 0 ? round(($succ / $total) * 100, 2) : 0;
        $text = '[' . $name . '] Access Log Total: ' . $total . ' ' . $codes . ' Succ:' . $rate . "%\n";
        return ['total' => $total, 'codes' => $codes, 'text' => $text, 'rate' => $rate];
    }

    protected function getNginxTotalData($name, $data)
    {
        $total = 0;
        $succ = 0;
        $codes = '';
        foreach ($data as $k => $v) {
            $total += $v;
            $codes .= " Code-{$k}: " . $v;
            if ($k == 200 || $k == 302) {
                $succ += $v;
            }
        }
        $rate = $total > 0 ? round(($succ / $total) * 100, 2) : 0;
        $text = '[' . $name . '] Access Log Total: ' . $total . ' ' . $codes . ' Succ:' . $rate . "%\n";
        return ['total' => $total, 'codes' => $codes, 'rate' => $rate, 'text' => $text];
    }

    protected function reports()
    {
        function getSym1($n1, $n2)
        {
            return $n1 == $n2 ? '(0)' : ($n1 > $n2 ? '(+' . round($n1 - $n2, 2) . ')' : '(-' . round($n2 - $n1, 2) . ')');
        }

        $h = date('H');
        // $h1 = $h - 1;
        $h1 = $h;
        $countDate = date('Y-m-d');
        $yesterday = date('Y-m-d', strtotime('-1 days'));
        $b7d = date('Y-m-d', strtotime('-7 days'));
        $b14d = date('Y-m-d', strtotime('-14 days'));

        $hostname = gethostname();
        $res = ModelWebLogAnalysis::where('count_date', $countDate)->where('host', $hostname)->first();
        $res2 = ModelWebLogAnalysis::where('count_date', $yesterday)->where('host', $hostname)->first();
        $content2 = !empty($res2) ? json_decode($res2->content, true) : [];

        $res3 = ModelWebLogAnalysis::where('count_date', $b7d)->where('host', $hostname)->first();
        $content3 = !empty($res3) ? json_decode($res3->content, true) : [];

        $res4 = ModelWebLogAnalysis::where('count_date', $b14d)->where('host', $hostname)->first();
        $content4 = !empty($res4) ? json_decode($res4->content, true) : [];

        if (!empty($res)) {
            $content = json_decode($res->content, true);
            $time = date('Y-m-d H:i:s');
            $message = "Time: {$time} [{$hostname}] Report:" . "\n";
            $eachs = ['H5', 'GM', 'Merchant', 'Analysis', 'Server'];
            foreach ($eachs as $sk) {
                $sym1 = [];
                $sym2 = [];
                $res = $this->getNginxTotalData($sk, $content['nginx'][$sk]['struct1']['total']);
                $message .= $res['text'];
                $c0 = 0;
                $r0 = 0;
                if ($h1 >= 0) {
                    $res = $this->getNginxHourData($sk . ' Last hour(' . $h1 . ')', $content['nginx'][$sk]['struct1']['hours'], $h1);
                    $message .= $res['text'];
                    $c0 = $res['total'];
                    $r0 = $res['rate'];
                    $sym1[] = $res['total'];
                    $sym2[] = $res['rate'];

                    if ($c0 < 1000 && false) {
                        continue;
                    }
                }

                if ($h1 >= 0 && !empty($content2)) {
                    $res = $this->getNginxHourData($sk . ' -1 days hour(' . $h1 . ')', $content2['nginx'][$sk]['struct1']['hours'], $h1);
                    $message .= $res['text'];
                    $sym1[] = $res['total'] . getSym1($c0, $res['total']);
                    $sym2[] = $res['rate'] . getSym1($r0, $res['rate']);
                }

                if ($h1 >= 0 && !empty($content3)) {
                    $res = $this->getNginxHourData($sk . ' -7 days hour(' . $h1 . ')', $content3['nginx'][$sk]['struct1']['hours'], $h1);
                    $message .= $res['text'];
                    $sym1[] = $res['total'] . getSym1($c0, $res['total']);
                    $sym2[] = $res['rate'] . getSym1($r0, $res['rate']);
                }

                if ($h1 >= 0 && !empty($content4)) {
                    $res = $this->getNginxHourData($sk . ' -14 days hour(' . $h1 . ')', $content4['nginx'][$sk]['struct1']['hours'], $h1);
                    $message .= $res['text'];
                    $sym1[] = $res['total'] .  getSym1($c0, $res['total']);
                    $sym2[] = $res['rate'] . getSym1($r0, $res['rate']);
                }

                if (!empty($sym1)) {
                    $message .= 'Hours Compare: ' . implode(' / ', $sym1) . "\n" . 'Succ Compare: ' . implode(' / ', $sym2) . "\n";
                }

                $message .= "\n";
            }
            $num =  isset($content['php']) && isset($content['php']['php']['struct1']['total']['ERROR']) ? $content['php']['php']['struct1']['total']['ERROR'] : 0;
            $num2 =  isset($content['php']) && isset($content['php']['php']['struct1']['total']['WARNING']) ? $content['php']['php']['struct1']['total']['WARNING'] : 0;
            if ($num > 0 || $num2 > 0 || true) {
                $message .= "[PHP] Error: $num Warning: $num2 \n";
            }

            $num = isset($content['laravel']) && isset($content['laravel']['laravel']['struct1']['ERROR']) ? $content['laravel']['laravel']['struct1']['ERROR'] : 0;
            $num2 = isset($content['laravel']) && isset($content['laravel']['laravel']['struct1']['WARNING']) ? $content['laravel']['laravel']['struct1']['WARNING'] : 0;
            if ($num > 0 || $num2 > 0 || true) {
                $message .= "[Laravel] Error: $num Warning: $num2 \n";
            }
        } else {
            $h = date('H:i:s');
            $message = "Time: {$h} [{$hostname}] Report:" . "\n";
            $message .= 'count data is empty!' . "\n";
        }

        if ($this->testing == false) {
            Artisan::queue('SendMessage', [
                'text' => $message
            ])->onConnection('redis')->onQueue('default');
            echo $message . PHP_EOL;
        }
        return $message;
    }
}
