<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\Redis;
use App\Http\Controllers\Controller;

class ApiController extends Controller
{
    public function err($code = 400, $msg = "")
    {
        $errors = config('api.errors');
        if (empty($msg) && isset($errors[$code])) {
            $msg = $errors[$code];
        }
        return ['code' => $code, 'message' => $msg, 'data' => [], "success" => 0];
    }

    public function succ($data, $msg = "", $code = 200 )
    {
        return ['code' => $code, 'message' => $msg, 'data' => $data, "success" => 1];
    }

    protected function requestLimit($key, $prefix = 'requestLimit:', $time = 15, $cnt = 1)
    {
        $redis = Redis::connection('cache');
        $num = $redis->incr($prefix . $key);
        $redis->expire($prefix . $key, $time);
        return $num <= $cnt ? true : false;
    }

    protected function clearLimit($key, $prefix = 'requestLimit:')
    {
        $redis = Redis::connection('cache');
        $redis->del($prefix . $key);
    }
}
