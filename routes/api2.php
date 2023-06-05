<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\IMCallbackController;
use App\Http\Controllers\Api\ClubController;
use App\Http\Controllers\Api\TestController;
Route::domain(env('DOMAIN_API'))
    ->withoutMiddleware('throttle:api')
    ->middleware(['maintenance', 'throttle:600:1'])
    ->group(function () {

        Route::post('/imcallback', [IMCallbackController::class, 'index']);
        Route::match(['GET', 'POST'], '/imcallback/test', [IMCallbackController::class, 'test']);

        Route::middleware(['apiAuth'])->group(function () {
            Route::post('/club/getList', [ClubController::class, 'getList']);
            Route::post('/club/add', [ClubController::class, 'add']);
            Route::post('/club/add2', [ClubController::class, 'add2']);
            Route::post('/club/destroy', [ClubController::class, 'destroys']);
            Route::post('/club/quit', [ClubController::class, 'quit']);
            Route::post('/club/edit', [ClubController::class, 'edit']);
            Route::post('/club/info', [ClubController::class, 'info']);

            Route::post('/club/getMemberList', [ClubController::class, 'getMemberList']);
            Route::post('/club/addGroupMember', [ClubController::class, 'addGroupMember']);
        });

        Route::get('/club/mergeface/{id}.jpg', [ClubController::class, 'mergeFace']);
        Route::get('/test/image.jpg', [TestController::class, 'imageMerge']);
    });

