<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\GM\HomeController;
use App\Http\Controllers\GM\ServerController;
use App\Http\Controllers\GM\RoomController;
use App\Http\Controllers\GM\AdminController;
use App\Http\Controllers\GM\DevToolsController;

Route::domain(env('DOMAIN_GM'))->middleware('ipWhite:GM_IPWHITE')->group(function () {


    Route::get('login', [HomeController::class, 'login']);
    Route::post('login', [HomeController::class, 'doLogin']);
    Route::get('loginout', [HomeController::class, 'doLoginout']);

    Route::middleware('adminAuth:GM')->group(function () {
        Route::get('/', [HomeController::class, 'index']);
        Route::get('manager/account/password/view', [HomeController::class, 'passwordView']);
        Route::post('manager/account/password', [HomeController::class, 'passwordEdit']);
        Route::get('manager/account/googlecode/view', [HomeController::class, 'googleCodeView']);
        Route::post('manager/account/googlecode', [HomeController::class, 'googleCodeEdit']);

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


        Route::get('server/room/view', [RoomController::class, 'roomView']);
        Route::get('server/room', [RoomController::class, 'roomList']);
        Route::get('server/room/{id}', [RoomController::class, 'roomDetail']);
        Route::post('server/room', [RoomController::class, 'roomAdd']);
        Route::patch('server/room/{id}', [RoomController::class, 'roomEdit']);
        Route::patch('server/room/enabled/{id}', [RoomController::class, 'roomEnabledEdit']);
        Route::delete('server/room/{id}', [RoomController::class, 'roomDel']);

        Route::get('devtools/webloganalysis/view', [DevToolsController::class, 'webLogAnalysisView']);
        Route::get('devtools/webloganalysis', [DevToolsController::class, 'webLogAnalysisList']);

        Route::get('devtools/config/view', [DevToolsController::class, 'configView']);
        Route::get('devtools/config', [DevToolsController::class, 'configDetail']);
        Route::patch('devtools/config', [DevToolsController::class, 'configEdit']);

        Route::post('admin/setLang/{lang}', [AdminController::class, 'setLang']);
    });
});
