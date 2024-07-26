<?php

use App\Http\Controllers\Api\{
    AuthController,
    GameController,
    GameFeedbackController,
    GameSubmissionController,
    GeoController,
    OtpController,
    SocialAuthController,
    SportEventController,
    SportRadarController,
    UserBiometricController,
    UserLeaderboardController,
    FriendController,
    NotificationController,
    UserSettingController,
};
use App\Models\Friend;
use Illuminate\Support\Facades\Route;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


// Route::get('fcm-test', [AuthController::class, 'FcmTest']);
Route::post('social/login', [SocialAuthController::class, 'login']);
Route::post('sign-up', [AuthController::class, 'signUp']);
Route::post('login', [AuthController::class, 'login']);
Route::post('biometric/login', [AuthController::class, 'biometric_login']);
Route::post('forgot-password', [AuthController::class, 'forgotPassword']);

Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::post('register', [AuthController::class, 'register'])->middleware('scope:register');
    Route::post('reset-password', [AuthController::class, 'resetPassword'])->middleware('scope:reset-password');
    Route::post('subscription/create', [AuthController::class, 'resetPassword'])->middleware('scope:reset-password');

    Route::group(['prefix' => 'auth'], function () {

        Route::group(['middleware' => ['scope:email-otp,password-otp,reset-password'], 'prefix' => 'otp'], function () {
            Route::get('/resend', [OtpController::class, 'resendOtp']);
            Route::post('/verify', [OtpController::class, 'verifyOtp']);
        });

        /**
         *  Routes with [*] access scopes!
         */
        Route::group(['middleware' => ['scope:*']], function () {

            Route::get('/user/profile', [AuthController::class, 'userProfile']);
            Route::post('user/change-password', [AuthController::class, 'changePassword']);
            Route::post('/user/update/profile', [AuthController::class, 'userProfileUpdate']);
            // Route::post('/user/update/setting', [UserSettingController::class, 'userSettingUpdate']);
            // Route::get('/user/setting', [UserSettingController::class, 'userSetting']);
            Route::get('/user/game/list', [GameController::class, 'userGameList']);
            Route::get('/user/game-detail/{id}', [UserLeaderboardController::class, 'eventLeaderboard']);
            Route::post('/user/biometric', [UserBiometricController::class, 'userBiometric']);
            Route::get('/user/rewards', [AuthController::class, 'userRewards']);
            Route::get('/logout', [AuthController::class, 'logout']);

            Route::group(['prefix' => 'game'], function () {
                Route::get('/list/{eventID}', [GameController::class, 'list']);
                Route::get('/{id}', [GameController::class, 'getGame']);
                Route::post('/feedback/store', [GameFeedbackController::class, 'store']);
            });

            Route::group(['prefix' => 'game-submission'], function () {
                Route::post('/store', [GameSubmissionController::class, 'store']);
            });


            // Route::group(['prefix' => 'sport-event'],function(){
            //     Route::get('/list',[SportEventController::class,'list']);
            // });

            Route::group(['prefix' => 'sport-radar'], function () {
                Route::get('/seasons/list', [SportRadarController::class, 'getSeasons']);
                Route::get('/season/schedule/{seasonID}', [SportRadarController::class, 'getSeasonSchedule']);
            });

            Route::group(['prefix' => 'sport-event'], function () {
                Route::get('/', [SportEventController::class, 'sportEvents']);
                Route::get('/list', [SportEventController::class, 'list']);
            });

            Route::group(['prefix' => 'user-leaderboard'], function () {
                Route::get('/list', [UserLeaderboardController::class, 'index']);
            });


        });

        Route::group(['prefix' => 'friends'], function () {
            Route::post('/add-friend', [FriendController::class, 'AddFriend']);
            Route::post('/accept-friend', [FriendController::class, 'AcceptFriend']);
            Route::get('/friend-list', [FriendController::class, 'FriendList']);
            Route::post('/decline-friend', [FriendController::class, 'DeclineFriend']);
        });

        Route::group(['prefix' => 'notifications'], function () {
            Route::get('/list', [NotificationController::class, 'NotificationList']);
        });


    });
});

Route::get('/countries', [GeoController::class, 'getCountries']);
Route::get('/cities/{id}', [GeoController::class, 'getCities']);
