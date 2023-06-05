<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Http\Library\MerchantCB;
use App\Models\ServerRequestLog;
use Illuminate\Support\Facades\Log;
use Artisan;

class Ts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'Ts {path}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ts';

    /**
     * Execute the console command.
     *
     * @return int
     */
    protected function templateT($path)
    {
        // $path = $this->argument('path');
        echo "templateT: " . $path . PHP_EOL;
        $keys = [];
        $content = file_get_contents($path);

        // global str_replace
        // $content = str_replace(': </', '</', $content);
        // $content = str_replace(':</', '</', $content);
        $content = str_replace('value="Close"', 'value="{{ __(\'Close\') }}"', $content);
        $content = str_replace('value="Submit"', 'value="{{ __(\'Submit\') }}"', $content);

        $content = str_replace(' Search', '{{ __(\' Search\') }}', $content);
        $content = str_replace(' Create', '{{ __(\' Create\') }}', $content);

        // a btn
        $res = preg_match_all("/>([a-zA-Z0-9 ^<\$]+)<\/a>/", $content, $outs);
        // print_r($outs);
        if ($res) {
            foreach ($outs[1] as $k => $v) {
                $new = str_replace($v, '{{ __(\'' . 'ts.' . $v . '\') }}', $outs[0][$k]);
                $new = trim(str_replace([":", "："], "", $new));
                $content = str_replace($outs[0][$k], $new, $content);
                // echo $new . PHP_EOL;
                $keys[] = $v;
            }
        }

        // modal title
        $res = preg_match_all("/<h4 class=\"modal-title\">([^<]+)<\/h4>/", $content, $outs);
        // print_r($outs);
        if ($res) {
            foreach ($outs[1] as $k => $v) {
                $new = str_replace($v, '{{ __(\'' . 'ts.' . $v . '\') }}', $outs[0][$k]);
                $new = trim(str_replace([":", "："], "", $new));
                $content = str_replace($outs[0][$k], $new, $content);
                // echo $new . PHP_EOL;
                $keys[] = $v;
            }
        }

        // label
        $res = preg_match_all("/>([^<]+)<\/label>/", $content, $outs);
        // print_r($outs);
        if ($res) {
            foreach ($outs[1] as $k => $v) {
                $new = str_replace($v, '{{ __(\'' . 'ts.' . $v . '\') }}', $outs[0][$k]);
                $new = trim(str_replace([":", "："], "", $new));
                $content = str_replace($outs[0][$k], $new, $content);
                // echo $new . PHP_EOL;
                $keys[] = $v;
            }
        }

        // p
        $res = preg_match_all("/>([^<{\$]+)<\/p>/", $content, $outs);
        // print_r($outs);
        if ($res) {
            foreach ($outs[1] as $k => $v) {
                $new = str_replace($v, '{{ __(\'' . 'ts.' . $v . '\') }}', $outs[0][$k]);
                $new = trim(str_replace([":", "："], "", $new));
                $content = str_replace($outs[0][$k], $new, $content);
                // echo $new . PHP_EOL;
                $keys[] = $v;
            }
        }

        // placeholder
        $res = preg_match_all("/placeholder=\"([^\"]+)\"/", $content, $outs);
        // print_r($outs);
        if ($res) {
            foreach ($outs[1] as $k => $v) {
                $new = str_replace($v, '{{ __(\'' . 'ts.' . $v . '\') }}', $outs[0][$k]);
                $content = str_replace($outs[0][$k], $new, $content);
                // echo $new . PHP_EOL;
                $keys[] = $v;
            }
        }

        // js select  
        $res = preg_match_all("/\"key\", \"value\", \"([^\"]+)\"\)/", $content, $outs);
        // print_r($outs);
        if ($res) {
            foreach ($outs[1] as $k => $v) {
                $new = str_replace($v, '{{ __(\'' . 'ts.' . $v . '\') }}', $outs[0][$k]);
                $content = str_replace($outs[0][$k], $new, $content);
                // echo $new . PHP_EOL;
                $keys[] = $v;
            }
        }
        // echo $content . PHP_EOL;


        // js title
        $res = preg_match_all("/title: \"([^\"]+)\"/", $content, $outs);
        // print_r($outs);
        if ($res) {
            foreach ($outs[1] as $k => $v) {
                $new = str_replace($v, '{{ __(\'' . 'ts.' . $v . '\') }}', $outs[0][$k]);
                $content = str_replace($outs[0][$k], $new, $content);
                // echo $new . PHP_EOL;
                $keys[] = $v;
            }
        }
        // echo $content . PHP_EOL;
        file_put_contents($path, $content);
        $this->writeKeys($keys);
        // return 0;
    }

    protected function phpControllerT($path)
    {
        $keys = [];
        $content = file_get_contents($path);
        echo 'phpControllerT:' . $path . PHP_EOL;
        $res = preg_match_all("/, 'result' => ('[^']+')]/", $content, $outs);
        // print_r($outs);
        if ($res) {
            foreach ($outs[1] as $k => $v) {
                $v2 = substr($v, 1, strlen($v));
                $new = str_replace($v, '__(' . '\'ts.' . $v2 . ')', $outs[0][$k]);
                $content = str_replace($outs[0][$k], $new, $content);
                // echo $new . PHP_EOL;
                $keys[] = $v;
            }
        }
        // echo $content . PHP_EOL;
        file_put_contents($path, $content);
        $this->writeKeys($keys);
    }

    protected function phpMenuT($path)
    {
        $keys = [];
        $content = file_get_contents($path);
        echo 'phpMenuT:' . $path . PHP_EOL;
        $res = preg_match_all("/'name' => ('[^']+')/", $content, $outs);
        // print_r($outs);
        if ($res) {
            foreach ($outs[1] as $k => $v) {
                $v2 = substr($v, 1, strlen($v));
                $new = str_replace($v, '\'ts.' . $v2, $outs[0][$k]);
                $content = str_replace($outs[0][$k], $new, $content);
                // echo $new . PHP_EOL;
                $keys[] = $v;
            }
        }
        // echo $content . PHP_EOL;
        file_put_contents($path, $content);
        $this->writeKeys($keys);
    }

    protected function phpJsonFormConfigT($path)
    {
        $keys = [];
        $content = file_get_contents($path);
        echo 'phpJsonFormConfigT:' . $path . PHP_EOL;
        $res = preg_match_all("/'re' => ('[^']+')/", $content, $outs);
        // print_r($outs);
        if ($res) {
            foreach ($outs[1] as $k => $v) {
                $v2 = substr($v, 1, strlen($v));
                $new = str_replace($v, '__(' . '\'ts.' . $v2 . ')', $outs[0][$k]);
                $content = str_replace($outs[0][$k], $new, $content);
                // echo $new . PHP_EOL;
                $keys[] = $v;
            }
        }
        // echo $content . PHP_EOL;
        // file_put_contents($path, $content);
        $this->writeKeys($keys);
    }

    protected function phpSelectItemsT($path)
    {
        $keys = [];
        $content = file_get_contents($path);
        echo 'phpSelectItemsT:' . $path . PHP_EOL;
        $content = str_replace('"', "'", $content);
        $res = preg_match_all("/=> ('[^\']+'),\n/", $content, $outs);

        // print_r($outs);
        if ($res) {
            foreach ($outs[1] as $k => $v) {
                $v2 = substr($v, 1, strlen($v));
                $new = str_replace($v, '\'ts.' . $v2, $outs[0][$k]);
                $content = str_replace($outs[0][$k], $new, $content);
                // echo $new . PHP_EOL;
                $keys[] = $v;
            }
        }

        $res = preg_match_all("/'value' => ('[^']+')/", $content, $outs);
        // print_r($outs);
        if ($res) {
            foreach ($outs[1] as $k => $v) {
                $v2 = substr($v, 1, strlen($v));
                $new = str_replace($v, '\'ts.' . $v2, $outs[0][$k]);
                $content = str_replace($outs[0][$k], $new, $content);
                // echo $new . PHP_EOL;
                $keys[] = $v;
            }
        }
        // echo $content . PHP_EOL;
        file_put_contents($path, $content);
        $this->writeKeys($keys);
    }

    protected function writeKeys($keys)
    {
        $num = 0;
        $ts1 = require base_path('resources/lang/en/ts.php');
        $ts2 = require base_path('resources/lang/zh-cn/ts.php');
        foreach ($keys as $k) {
            $k = trim(str_replace([":", "："], "", $k));
            $k = str_replace("'", '', $k);
            if (!isset($ts1[$k])) {
                $ts1[$k] = $k;
                // $num++;
            }

            if (!isset($ts2[$k])) {
                $ts2[$k] = '';
                $num++;
            }
        }
        echo 'keys num:' . count($keys) . PHP_EOL;
        echo 'add keys num:' . $num . PHP_EOL;
        file_put_contents(base_path('resources/lang/zh-cn/ts.php'), '<?php return ' . var_export($ts2, true) . ';');
        file_put_contents(base_path('resources/lang/en/ts.php'), '<?php return ' . var_export($ts1, true) . ';');
    }

    public function handle()
    {
        $path = $this->argument('path');
        if (is_numeric($path)) {
            $dirs = $this->dirs();
            $this->each($dirs[$path]);
        } else if ($path == 'dirs') {
            $dirs = $this->dirs();
            foreach ($dirs as $dir) {
                $this->each($dir);
            }
        } else {
            $this->each($path);
        }
    }

    protected function each($path)
    {
        if (is_file($path)) {
            if (strpos($path, 'blade') !== false) {
                $this->templateT($path);
            }

            if (strpos($path, 'Controller') !== false) {
                $this->phpControllerT($path);
            }

            if (strpos($path, 'menu') !== false) {
                $this->phpMenuT($path);
            }

            if (strpos($path, 'selectItems') !== false) {
                $this->phpSelectItemsT($path);
            }

            if (strpos($path, 'json_form_config') !== false) {
                $this->phpJsonFormConfigT($path);
            }
        } else if (is_dir($path)) {
            foreach (glob($path . '/*.php') as $p) {
                // echo $p . PHP_EOL;
                if (is_file($p) && strpos($p, 'blade') !== false) {
                    $this->templateT($p);
                }

                if (strpos($p, 'Controller') !== false) {
                    $this->phpControllerT($p);
                }

                if (strpos($p, 'menu') !== false) {
                    $this->phpMenuT($p);
                }

                if (strpos($p, 'selectItems') !== false) {
                    $this->phpSelectItemsT($p);
                }

                if (strpos($p, 'json_form_config') !== false) {
                    $this->phpJsonFormConfigT($p);
                }
            }
        } else {
            echo 'no run' . PHP_EOL;
        }
    }

    protected function dirs()
    {
        return [
            // templates
            base_path('resources/views/GM/Admin/actionLogView.blade.php'),
            // base_path('resources/views/GM/Admin'),
            // base_path('resources/views/GM/Room'),
            // base_path('resources/views/GM/Server'),

            // base_path('resources/views/Analysis/Customer'),
            // base_path('resources/views/Analysis/Player'),
            // base_path('resources/views/Analysis/DashboardView.blade.php'),

            // base_path('resources/views/Merchant/Admin'),
            // base_path('resources/views/Merchant/Report'),
            // base_path('resources/views/Merchant/User'),

            // // php
            // base_path('app/Http/Controllers/Analysis'),
            // base_path('app/Http/Controllers/GM'),
            // base_path('app/Http/Controllers/Merchant'),

            // base_path('config/analysis/analysis.menu.php'),
            // base_path('config/analysis/selectItems.php'),

            // base_path('config/gm/gm.menu.php'),
            // base_path('config/gm/selectItems.php'),
            // base_path('config/gm/json_form_config'),

            // base_path('config/merchant/merchant.menu.php'),
            // base_path('config/merchant/selectItems.php'),
        ];
    }
}
