<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Redis;
use Illuminate\Http\Request;
use App\Http\Library\Comm;

class Maintenance
{
    public function handle(Request $request, Closure $next)
    {
        $ipWhite = env('TEST_IPWHITE');
        $ipWhite = explode(',', $ipWhite);
        $ip = Comm::getIP($request);
        
        if (in_array($ip, $ipWhite)) {
            return $next($request);
        }

        if (env('MAINTENANCE')) {
            return response()->json(['error' => [
                'code' => 9999,
                'message' => 'IG Games In maintenance'
            ], "data" => null]);
        }

        $redis = Redis::connection('cache');
        $stop = (int) $redis->get('stop_api_service');
        if ($stop == 1) {
            return response()->json(['error' => [
                'code' => 9998,
                'message' => 'IG Games is under maintenance'
            ], "data" => null]);
        }
        return $next($request);
    }
}
