<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Http\Library\Comm;
class IPWhite
{
    public function handle(Request $request, Closure $next, $type)
    {

        $ipWhite = env($type);
        if (empty($ipWhite)) {
            return $next($request);
        }

        $ipWhite = explode(',', $ipWhite);
        $ip = Comm::getIP($request);
        if (!in_array($ip, $ipWhite)) {
            return response('IP Limited. your ip address: ' . $ip, 403);
        }
        return $next($request);
    }
}
