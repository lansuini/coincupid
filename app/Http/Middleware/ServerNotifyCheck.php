<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Hashids\Hashids;
class ServerNotifyCheck
{
    public function handle(Request $request, Closure $next)
    {
        $oc = env('SERVER_NOTIFY_CHECK', true);
        if ($oc) {
            $t = $request->query->get('_t');
            $s = $request->query->get('_s');

            $hashids = new Hashids(env('SERVER_HASH_IDS_SALT'), 32, env('SERVER_HASH_IDS_STR_TABLE'));
            $ns = $hashids->decode($s);
            $nt = $ns[0] ?? '';
            if ($nt != $t) {
                return response(['status' => 1, 'desc' => 'check sign fail']);
            }

            if ($t > time() + 15 * 60 || $t < time() - 15 * 60) {
                return response(['status' => 1, 'desc' => 'sign expired']);
            }
        }
        return $next($request);
    }
}
