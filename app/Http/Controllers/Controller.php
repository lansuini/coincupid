<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use App\Http\Library\Comm;
class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected function redirectPage($success, $message, $url, $waitTime = 3)
    {
        return view('GM/redirect', ['success' => $success, 'message' => $message, 'url' => $url, 'waitTime' => $waitTime]);
    }

    protected function success($result, $extends = []) {
        return array_merge(['success' => 1, 'result' => $result], $extends);
    }

    protected function error($data, $extends = []) {
        return array_merge(['success' => 0, 'result' => $data], $extends);
    }

    protected function ip(Request $request) {
        return Comm::getIP($request);
    }
}
