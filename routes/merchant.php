<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Merchant\HomeController;
use App\Http\Controllers\Merchant\UserController;
use App\Http\Controllers\Merchant\ReportController;
use App\Http\Controllers\Merchant\SupportController;
use App\Http\Controllers\GM\AdminController;

Route::domain(env('DOMAIN_MERCHANT'))->middleware('ipWhite:MERCHANT_IPWHITE')->group(function () {


    Route::get('login', [HomeController::class, 'login']);
    Route::post('login', [HomeController::class, 'doLogin']);
    Route::get('loginout', [HomeController::class, 'doLoginout']);

    Route::middleware('adminAuth:MERCHANT')->group(function () {
        Route::get('/', [HomeController::class, 'index']);

        Route::get('manager/account/password/view', [HomeController::class, 'passwordView']);
        Route::post('manager/account/password', [HomeController::class, 'passwordEdit']);
        Route::get('manager/account/googlecode/view', [HomeController::class, 'googleCodeView']);
        Route::post('manager/account/googlecode', [HomeController::class, 'googleCodeEdit']);

        Route::get('getbasedata', [HomeController::class, 'getBaseData']);

        Route::get('user/enterexitroomwinlose/view', [UserController::class, 'userEnterExitRoomWinLoseView']);
        Route::get('user/enterexitroomwinlose', [UserController::class, 'userEnterExitRoomWinLoseList']);

        Route::get('user/account/view', [UserController::class, 'accountView']);
        Route::get('user/account', [UserController::class, 'accountList']);

        Route::get('report/total/view', [ReportController::class, 'totalView']);
        Route::get('report/total', [ReportController::class, 'totalList']);

        Route::get('report/day/view', [ReportController::class, 'dayView']);
        Route::get('report/day', [ReportController::class, 'dayList']);

        Route::get('report/datareport/view', [ReportController::class, 'dataReportView']);
        Route::get('report/datareport', [ReportController::class, 'dataReportList']);

        Route::get('support/apidocument/view', [SupportController::class, 'apiDocumentView']);
        Route::get('support/apidocument', [SupportController::class, 'apiDocumentList']);

        Route::post('admin/setLang/{lang}', [AdminController::class, 'setLang']);
    });
});
