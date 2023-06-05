<?php

namespace App\Http\Library;

use Illuminate\Http\Request;

class Comm {
    static function getIP(Request $request) {
        $ip1 = $request->ip();
        $ip2 = $request->header('X_FORWARDED_FOR');
        return !empty($ip2) ? $ip2 : $ip1;
    }
}