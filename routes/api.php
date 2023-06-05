<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\IMCallbackController;
use App\Http\Controllers\Api\TestController;
use App\Http\Controllers\Api\AccountController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\HomeController;
use App\Http\Controllers\Api\UserDynamicController;
use App\Http\Controllers\Api\DiscoverController;
use App\Http\Controllers\Api\AccountConfigController;

Route::domain(env('DOMAIN_API'))
    ->withoutMiddleware('throttle:api')
    ->middleware(['maintenance', 'throttle:600:1'])
    ->group(function () {
        // Route::post('imcallback', [IMCallbackController::class, 'index']);
        //        Route::get('/account/getInfotest', [AccountController::class, 'getInfotest']);
        Route::get('/', [IMCallbackController::class, 'index']);
        Route::middleware(['googleRecaptchaAuth'])->group(function () {
            Route::post('/account/mobileCaptcha', [AuthController::class, 'sendMobileCaptcha']);
            Route::post('/account/loginByMobileAndCaptcha', [AuthController::class, 'loginByMobileAndCaptcha']);
            Route::post('/account/loginByAccountAndPassword', [AuthController::class, 'loginByAccountAndPassword']);
            Route::post('/account/loginByAnonymous', [AuthController::class, 'loginByAnonymous']);
        });

        Route::get('/account/countryCode', [AuthController::class, 'getCountryCode']);

        Route::middleware(['apiAuth'])->group(function () {
            Route::post('/account/loginOut', [AuthController::class, 'loginOut']);
            Route::post('/account/info', [AuthController::class, 'setInfo']);
            Route::post('/account/bindMobile', [AuthController::class, 'bindMobile']);
            Route::get('/account/getInfo', [AccountController::class, 'getInfo']);
            Route::get('/account/IMInfo', [AuthController::class, 'getIMInfo']);
            Route::get('/account/deleteAccountStatus', [AuthController::class, 'getDeleteAccountStatus']);
            Route::post('/account/mobileCaptchaByUid', [AuthController::class, 'sendMobileCaptchaByUid']);
            Route::post('/account/deleteAccount', [AuthController::class, 'deleteAccount']);
            // Route::get('/account/tags', [AccountController::class, 'getTags']);
            Route::get('/account/getInfoByImid/{imid}', [AccountController::class, 'getInfoByImid'])->where(['imid' => '^(?=.*[a-zA-Z])(?=.*\d)[a-zA-Z\d]{6,10}$']);
            Route::get('/account/getInfoByUid/{uuid}', [AccountController::class, 'getInfoByUid'])->where(['uuid' => '[0-9]+']);
            Route::post('/account/setCoordinate', [AccountController::class, 'setCoordinate']);
            Route::post('/account/setNickname', [AccountController::class, 'setNickname']);
            Route::post('/account/setBirthday', [AccountController::class, 'setBirthday']);
            Route::post('/account/setSex', [AccountController::class, 'setSex']);
            Route::get('/account/getIntention', [AccountController::class, 'getIntention']);
            Route::post('/account/setIntention', [AccountController::class, 'setIntention']);
            Route::post('/account/uploadAvatar', [AccountController::class, 'uploadAvatar']);
            Route::post('/account/setAvatar', [AccountController::class, 'setAvatar']);
            Route::get('/account/getAuthRadom', [AccountController::class, 'getAuthRadom']);
            Route::get('/account/getTagAttrs', [AccountController::class, 'getTagAttrs']);
            Route::get('/account/getTags', [AccountController::class, 'getTags']);
            Route::post('/account/setTags', [AccountController::class, 'setTags']);
            Route::post('/account/addTag', [AccountController::class, 'addTag']);
            Route::post('/account/setSocialAccount', [AccountController::class, 'setSocialAccount']);
            Route::post('/account/editInfo', [AccountController::class, 'editInfo']);
            Route::get('/account/applyVodSignature', [AccountController::class, 'applyVodSignature']);
            Route::post('/account/setPerfVideo', [AccountController::class, 'setPerfVideo']);

            Route::post('/account/setEducational', [AccountController::class, 'setEducational']);
            Route::post('/account/setIncome', [AccountController::class, 'setIncome']);
            Route::post('/account/setPortrait', [AccountController::class, 'setPortrait']);
            //            Route::post('/account/uploadPortrait', [AccountController::class, 'uploadPortrait']);
            Route::get('/account/getOccupationCates', [AccountController::class, 'getOccupationCates']);
            Route::get('/account/getOccupationPosts', [AccountController::class, 'getOccupationPosts']);
            Route::post('/account/setOccupation', [AccountController::class, 'setOccupation']);
            Route::post('/account/setVerifyVideo', [AccountController::class, 'setVerifyVideo']);

            Route::get('/home/getNewcomerUserList', [HomeController::class, 'getNewcomerUserList']);
            Route::get('/home/getActiveUserList', [HomeController::class, 'getActiveUserList']);
            Route::get('/home/getNearbyUserList', [HomeController::class, 'getNearbyUserList']);
            Route::get('/home/searchUser', [HomeController::class, 'searchUser']);

            //世界墙
            Route::post('/dynamic/uploadImage', [UserDynamicController::class, 'uploadImage']);
            Route::post('/dynamic/publish', [UserDynamicController::class, 'publishDynamic']);
            Route::post('/dynamic/like/{dynamicId}', [UserDynamicController::class, 'postLike'])->where(['dynamicId' => '[0-9]+']);
            Route::post('/dynamic/comment/{dynamicId}', [UserDynamicController::class, 'postComment'])->where(['dynamicId' => '[0-9]+']);

            Route::get('/dynamic/comments/{dynamicId}', [UserDynamicController::class, 'getCommentsById'])->where(['dynamicId' => '[0-9]+']);
            Route::get('/dynamic/replies/{commentId}', [UserDynamicController::class, 'getRepliesById'])->where(['commentId' => '[0-9]+']);
            Route::get('/dynamic/list/{uuid}', [UserDynamicController::class, 'getDynamics'])->where(['uuid' => '[0-9]+']);

            Route::get('/dynamic/{dynamicId}', [UserDynamicController::class, 'getDynamicById'])->where(['dynamicId' => '[0-9]+']);
            Route::post('/dynamic/setDynamicAuth/{dynamicId}', [UserDynamicController::class, 'setDynamicAuth'])->where(['dynamicId' => '[0-9]+']);
            Route::post('/dynamic/delDynamic/{dynamicId}', [UserDynamicController::class, 'delDynamic'])->where(['dynamicId' => '[0-9]+']);

            Route::get('/dynamic/notices', [UserDynamicController::class, 'notices']);
            Route::post('/dynamic/batchReadNotices', [UserDynamicController::class, 'batchReadNotices']);
            Route::post('/dynamic/readNotice/{notice_id}', [UserDynamicController::class, 'readNotice'])->where(['notice_id' => '[0-9]+']);
            //发现
            Route::get('/discover/popularUser', [DiscoverController::class, 'popularUser']);
            Route::get('/discover/voteUsers/{uuid}', [DiscoverController::class, 'getVoteUsersByUid'])->where(['uuid' => '[0-9]+']);
            Route::get('/discover/dynamics', [DiscoverController::class, 'getDynamics']);
            Route::get('/discover/popUserList', [DiscoverController::class, 'popUserList']);
            Route::post('/discover/popularVote/{uuid}', [DiscoverController::class, 'popularVote'])->where(['uuid' => '[0-9]+']);

            //v3
            Route::post('/account/config/privacySet', [AccountConfigController::class, 'privacySet']);
            Route::get('/account/config', [AccountConfigController::class, 'getAccountConfig']);

            Route::post('/account/batchReadSystemNotices', [AccountController::class, 'batchReadSystemNotices']);
            Route::post('/account/batchDelSystemNotices', [AccountController::class, 'batchDelSystemNotices']);

            Route::post('/account/resetPassword', [AccountController::class, 'resetPassword']);
        });
    });

Route::domain(env('DOMAIN_CALLBACK'))
    ->withoutMiddleware('throttle:api')
    ->middleware(['maintenance', 'throttle:600:1'])
    ->group(function () {
        Route::post('imcallback', [IMCallbackController::class, 'index']);
        Route::get('/', [IMCallbackController::class, 'index']);

        Route::get('/test/imUserSig', [TestController::class, 'imUserSig']);
        Route::get('/test/imrestApi', [TestController::class, 'imrestApi']);
        Route::get('/test/imtestfriend', [TestController::class, 'imtestfriend']);
    });
// Route::get('/', [IMCallbackController::class, 'index']);