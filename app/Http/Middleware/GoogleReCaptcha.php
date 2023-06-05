<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Http\Request;

class GoogleReCaptcha
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->header('GToken');
        $action = $request->header('GAction');
        if (env('RECAPTCHA_V3_ENABLED', 0) == 0) {
            return $next($request);
        }
        
        if (empty($token) || empty($action)) {
            return response(['message' => "googleReCaptcha args is empty"]);
            return response(['code' => 910, 'message' => 'googleReCaptcha args is empty', 'data' => [], "success" => 0]);
        }

        // use the reCAPTCHA PHP client library for validation 
        $recaptcha = new \ReCaptcha\ReCaptcha(env('RECAPTCHA_V3_SECRET_KEY'));
        $resp = $recaptcha->setExpectedAction($action)
            ->setScoreThreshold(0.5)
            ->verify($token, $_SERVER['REMOTE_ADDR']);
        // verify the response 
        if ($resp->isSuccess()) {
            // valid submission 
            // go ahead and do necessary stuff 
        } else {
            // collect errors and display it 
            $errors = $resp->getErrorCodes();
            // return response(['message' => $errors]);
            return response(['code' => 911, 'message' => 'Login expired, please login in again', 'data' => ['errors' => $errors], "success" => 0]);
        }
        return $next($request);
    }
}
