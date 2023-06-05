<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Library\Comm;
use App\Http\Controllers\Api\TestController;
use App\Http\Controllers\Api\HomeController;
use App\Http\Controllers\Api\AccountConfigController;
Route::domain(env('DOMAIN_API'))->group(function () {
    Route::get('/', function (Request $request) {
        $ip = Comm::getIP($request);
        return response('IP Limited. your ip address: ' . $ip, 403);
    });
});

Route::domain(env('DOMAIN_API'))
    ->withoutMiddleware('throttle:api')
    ->middleware(['maintenance', 'throttle:600:1'])
    ->group(function () {
        Route::get('/testSettle', [TestController::class, 'testSettle']);
        Route::get('/testPay', [TestController::class, 'testPay']);
        Route::get('/testBalance', [TestController::class, 'testBalance']);
        Route::get('/testQueryPay', [TestController::class, 'testQueryPay']);
        Route::get('/testQuerySettle', [TestController::class, 'testQuerySettle']);
    });
Route::get('/testIp', [HomeController::class, 'testIp']);
Route::get('/getActiveUserList', [HomeController::class, 'getActiveUserList']);
Route::get('/testAccountConfig', [AccountConfigController::class, 'testAccountConfig']);
Route::get('/getNearbyUserList', [HomeController::class, 'getNearbyUserList']);