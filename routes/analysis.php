<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Analysis\HomeController;
use App\Http\Controllers\Analysis\ServerController;

use App\Http\Controllers\Analysis\AdminController;
use App\Http\Controllers\Analysis\UserController;
use App\Http\Controllers\Analysis\CustomerController;

use App\Http\Controllers\Merchant\AdminController as MerchantAdminController;
use App\Http\Controllers\Merchant\HomeController as MerchantHomeController;
use App\Http\Controllers\Analysis\ConfigSetController;

Route::domain(env('DOMAIN_ANALYSIS'))->middleware('ipWhite:ANALYSIS_IPWHITE')->group(function () {


    Route::get('login', [HomeController::class, 'login']);
    Route::post('login', [HomeController::class, 'doLogin']);
    Route::get('loginout', [HomeController::class, 'doLoginout']);

    Route::middleware('adminAuth:ANALYSIS')->group(function () {
        Route::get('/', [HomeController::class, 'index']);

        Route::get('dashboard/view', [HomeController::class, 'dashboardView']);
        Route::get('dashboard', [HomeController::class, 'dashboardList']);

        Route::get('user/account/view', [UserController::class, 'accountView']);
        Route::get('user/account', [UserController::class, 'accountList']);
        Route::get('user/accountdetail/{id}', [UserController::class, 'accountDetail']);
        Route::get('user/getOccupationTree', [UserController::class, 'getOccupationTree']);
        Route::patch('user/accountEdit/{id}', [UserController::class, 'accountEdit']);
        Route::patch('user/accountStatusEdit/{id}', [UserController::class, 'accountStatusEdit']);

        Route::get('user/loginlog/view', [UserController::class, 'loginLogView']);
        Route::get('user/loginlog', [UserController::class, 'loginLogList']);

        Route::get('user/playlog/view', [UserController::class, 'playLogView']);
        Route::get('user/playlog', [UserController::class, 'playLogList']);
        Route::get('user/playlogdetail', [UserController::class, 'playLogDetailList']);

        Route::get('user/goldlog/view', [UserController::class, 'goldlogView']);
        Route::get('user/goldlog', [UserController::class, 'goldLogList']);

        Route::get('player/realonlineplay/view', [UserController::class, 'realOnlinePlayView']);
        Route::get('player/realonlineplay', [UserController::class, 'realOnlinePlayList']);

        Route::get('player/livematch/view', [UserController::class, 'liveMatchView']);
        Route::get('player/livematch', [UserController::class, 'liveMatchList']);

        Route::get('player/online/view', [UserController::class, 'onlineView']);
        Route::get('player/online', [UserController::class, 'onlineList']);

        Route::get('player/roomwinlose/view', [UserController::class, 'roomWinLoseView']);
        Route::get('player/roomwinlose', [UserController::class, 'roomWinLoseList']);

        Route::get('player/datareport/view', [UserController::class, 'dataReportView']);
        Route::get('player/datareport', [UserController::class, 'dataReportList']);

        Route::get('customer/client/view', [CustomerController::class, 'clientView']);
        Route::get('customer/client', [CustomerController::class, 'clientList']);
        Route::get('customer/client/{id}', [CustomerController::class, 'clientDetail']);
        Route::post('customer/client', [CustomerController::class, 'clientAdd']);
        Route::patch('customer/client/{id}', [CustomerController::class, 'clientEdit']);
        Route::delete('customer/client/{id}', [CustomerController::class, 'clientDel']);

        Route::get('customer/serverrequestlog/view', [CustomerController::class, 'serverRequestLogView']);
        Route::get('customer/serverrequestlog', [CustomerController::class, 'serverRequestLogList']);
        Route::post('customer/serverrequestlog/{clientId}/{id}', [CustomerController::class, 'serverRequestLogAdd']);
        Route::get('customer/serverrequestlog/{clientId}/{id}', [CustomerController::class, 'serverRequestLogDetail']);
        Route::get('customer/serverpostlog/view', [CustomerController::class, 'serverPostLogView']);
        Route::get('customer/serverpostlog', [CustomerController::class, 'serverPostLogList']);


        Route::get('manager/account/password/view', [HomeController::class, 'passwordView']);
        Route::post('manager/account/password', [HomeController::class, 'passwordEdit']);
        Route::get('manager/account/googlecode/view', [HomeController::class, 'googleCodeView']);
        Route::post('manager/account/googlecode', [HomeController::class, 'googleCodeEdit']);


        Route::get('merchant/getbasedata', [MerchantHomeController::class, 'getBaseData']);
        Route::get('merchant/manager/account/view', [MerchantAdminController::class, 'adminView']);
        Route::get('merchant/manager/account', [MerchantAdminController::class, 'adminList']);
        Route::get('merchant/manager/account/{id}', [MerchantAdminController::class, 'adminDetail']);
        Route::post('merchant/manager/account', [MerchantAdminController::class, 'adminAdd']);
        Route::patch('merchant/manager/account/{id}', [MerchantAdminController::class, 'adminEdit']);
        Route::delete('merchant/manager/account/{id}', [MerchantAdminController::class, 'adminDel']);

        Route::get('merchant/manager/role/view', [MerchantAdminController::class, 'roleView']);
        Route::get('merchant/manager/role', [MerchantAdminController::class, 'roleList']);
        Route::get('merchant/manager/role/{id}', [MerchantAdminController::class, 'roleDetail']);
        Route::post('merchant/manager/role', [MerchantAdminController::class, 'roleAdd']);
        Route::patch('merchant/manager/role/{id}', [MerchantAdminController::class, 'roleEdit']);
        Route::delete('merchant/manager/role/{id}', [MerchantAdminController::class, 'roleDel']);

        Route::get('merchant/manager/loginlog/view', [MerchantAdminController::class, 'loginLogView']);
        Route::get('merchant/manager/loginlog', [MerchantAdminController::class, 'loginLogList']);

        Route::get('merchant/manager/actionlog/view', [MerchantAdminController::class, 'actionLogView']);
        Route::get('merchant/manager/actionlog', [MerchantAdminController::class, 'actionLogList']);
        Route::get('merchant/manager/actionlog/{id}', [MerchantAdminController::class, 'actionLogDetail']);


        Route::get('getbasedata', [HomeController::class, 'getBaseData']);

        Route::get('manager/account/view', [AdminController::class, 'adminView']);
        Route::get('manager/account', [AdminController::class, 'adminList']);
        Route::get('manager/account/{id}', [AdminController::class, 'adminDetail']);
        Route::post('manager/account', [AdminController::class, 'adminAdd']);
        Route::patch('manager/account/{id}', [AdminController::class, 'adminEdit']);
        Route::delete('manager/account/{id}', [AdminController::class, 'adminDel']);

        Route::get('manager/role/view', [AdminController::class, 'roleView']);
        Route::get('manager/role', [AdminController::class, 'roleList']);
        Route::get('manager/role/{id}', [AdminController::class, 'roleDetail']);
        Route::post('manager/role', [AdminController::class, 'roleAdd']);
        Route::patch('manager/role/{id}', [AdminController::class, 'roleEdit']);
        Route::delete('manager/role/{id}', [AdminController::class, 'roleDel']);

        Route::get('manager/loginlog/view', [AdminController::class, 'loginLogView']);
        Route::get('manager/loginlog', [AdminController::class, 'loginLogList']);

        Route::get('manager/actionlog/view', [AdminController::class, 'actionLogView']);
        Route::get('manager/actionlog', [AdminController::class, 'actionLogList']);
        Route::get('manager/actionlog/{id}', [AdminController::class, 'actionLogDetail']);

        Route::get('manager/currency/view', [AdminController::class, 'currencyView']);
        Route::get('manager/currency', [AdminController::class, 'currencyList']);
        Route::get('manager/currency/{id}', [AdminController::class, 'currencyDetail']);
        Route::post('manager/currency', [AdminController::class, 'currencyAdd']);
        Route::patch('manager/currency/{id}', [AdminController::class, 'currencyEdit']);
        Route::delete('manager/currency/{id}', [AdminController::class, 'currencyDel']);

        Route::get('server/game/room/view', [ServerController::class, 'roomView']);
        Route::get('server/game/room', [ServerController::class, 'roomList']);
        Route::get('server/game/room/{id}', [ServerController::class, 'roomDetail']);
        Route::post('server/game/room', [ServerController::class, 'roomAdd']);
        Route::patch('server/game/room/{id}', [ServerController::class, 'roomEdit']);
        Route::patch('server/game/room/enabled/{id}', [ServerController::class, 'roomEnabledEdit']);
        Route::match(['GET', 'PATCH'], '/server/game/room/json/{id}', [ServerController::class, 'roomJSONEdit']);
        Route::delete('server/game/room/{id}', [ServerController::class, 'roomDel']);
        Route::post('server/game/room/pushconfig', [ServerController::class, 'roomPushConfig']);

        Route::get('server/game/room/process/{id}', [ServerController::class, 'roomProcessDetail']);
        Route::patch('server/game/room/process/{id}', [ServerController::class, 'roomProcessEdit']);

        Route::get('server/game/room/inventory/{id}', [ServerController::class, 'roomInventoryDetail']);
        Route::patch('server/game/room/inventory/{id}', [ServerController::class, 'roomInventoryEdit']);


        Route::get('server/game/play/view', [ServerController::class, 'playView']);
        Route::get('server/game/play', [ServerController::class, 'playList']);
        Route::get('server/game/play/{id}', [ServerController::class, 'playDetail']);
        Route::post('server/game/play', [ServerController::class, 'playAdd']);
        Route::patch('server/game/play/{id}', [ServerController::class, 'playEdit']);
        Route::delete('server/game/play/{id}', [ServerController::class, 'playDel']);

        Route::get('server/game/processcontrol/view', [ServerController::class, 'processControlView']);
        Route::get('server/game/processcontrol', [ServerController::class, 'processControlList']);
        Route::get('server/game/processcontrol/{id}', [ServerController::class, 'processControlDetail']);
        Route::post('server/game/processcontrol', [ServerController::class, 'processControlAdd']);
        Route::patch('server/game/processcontrol/{id}', [ServerController::class, 'processControlEdit']);
        Route::delete('server/game/processcontrol/{id}', [ServerController::class, 'processControlDel']);

        Route::get('server/game/maintenance/view', [ServerController::class, 'maintenanceView']);
        Route::get('server/game/maintenance', [ServerController::class, 'maintenanceList']);
        Route::patch('server/game/maintenance', [ServerController::class, 'maintenanceEdit']);

        Route::post('admin/setLang/{lang}', [AdminController::class, 'setLang']);

        Route::get('configset/configtag/view', [ConfigSetController::class, 'configtagView']);
        Route::get('configset/configTag', [ConfigSetController::class, 'configTagList']);
        Route::post('configset/tagAdd', [ConfigSetController::class, 'tagAdd']);
        Route::get('configset/tagDetail/{id}', [ConfigSetController::class, 'tagDetail']);
        Route::patch('configset/tagEdit/{id}', [ConfigSetController::class, 'tagEdit']);
        Route::delete('configset/tagDel/{id}', [ConfigSetController::class, 'tagDel']);

        Route::get('configset/configAttrTag/view', [ConfigSetController::class, 'configAttrTagView']);
        Route::get('configset/configAttrTag', [ConfigSetController::class, 'configAttrTagList']);
        Route::post('configset/attrTagAdd', [ConfigSetController::class, 'attrTagAdd']);
        Route::get('configset/attrTagDetail/{id}', [ConfigSetController::class, 'attrTagDetail']);
        Route::patch('configset/attrTagEdit/{id}', [ConfigSetController::class, 'attrTagEdit']);
        Route::delete('configset/attrTagDel/{id}', [ConfigSetController::class, 'attrTagDel']);


        Route::get('configset/configIntention/view', [ConfigSetController::class, 'configIntentionView']);
        Route::get('configset/configIntention', [ConfigSetController::class, 'configIntentionList']);
        Route::post('configset/intentionAdd', [ConfigSetController::class, 'intentionAdd']);
        Route::get('configset/intentionDetail/{id}', [ConfigSetController::class, 'intentionDetail']);
        Route::patch('configset/intentionEdit/{id}', [ConfigSetController::class, 'intentionEdit']);
    });
});
