<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Http\Request;
use App\Models\Account;
use Illuminate\Support\Facades\Redis;
class ApiAuth
{
    public function handle(Request $request, Closure $next)
    {   
        $authorization = $request->header('Authorization');
        if (empty($authorization)) {
            return response(['code' => 900, 'message' => 'headars.Authorization not allow empty', 'data' => [], "success" => 0]);
        }

        $uid = Account::getUidByToken($authorization);
        if ($uid == 0) {
            return response(['code' => 901, 'message' => 'Login expired, please login in again', 'data' => [], "success" => 0]);
        }

        $request->merge(['uid' => $uid]);
        Redis::Hset($uid,'last_online_time',time());
        return $next($request);
    }
}
