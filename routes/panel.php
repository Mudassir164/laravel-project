<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\{
    AuthController,
    DemographicsController,
    OtpController,
    GameController,
    OrganizerController,
    RewardController,
    SportRadarController,
    UserController,
    SponsorController,
    SportEventController,
    SubscriptionItemController,
    NotificationController
};

/*
|--------------------------------------------------------------------------
| Panel Routes  
|--------------------------------------------------------------------------
|
*/

//Sign-up
Route::group(['prefix' => 'sign-up'], function () {
    Route::post('pub-owner', [AuthController::class, 'signUp'])->name('pub-owner');
    Route::post('sponsor', [AuthController::class, 'signUp'])->name('sponsor');
});


Route::post('login', [AuthController::class, 'login']);
Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
Route::get('subscription/items/list', [SubscriptionItemController::class, 'index']);


//Authenticated User Routes
Route::group(['middleware' => ['auth:sanctum']], function () {
    
    //Registration
    Route::group(['middleware' => 'scope:register-web', 'prefix' => 'register'], function () {
        Route::post('/pub-owner', [AuthController::class, 'registerPubOwner']);
        Route::post('/sponsor', [AuthController::class, 'registerSponsor']);
    });
    
    //Reset Password
    Route::post('reset-password', [AuthController::class, 'resetPassword'])->middleware('scope:reset-password-web');
    
    //OTP
    Route::group(['middleware' => ['scope:email-otp,password-otp,reset-password'], 'prefix' => 'otp'], function () {
        Route::get('/resend', [OtpController::class, 'resendOtp']);
        Route::post('/verify', [OtpController::class, 'verifyOtp']);
    });

    Route::group(['middleware' => ['scope:web']], function () {

        Route::get('/user/profile', [AuthController::class, 'userProfile']);
        Route::post('/user/change-password', [AuthController::class, 'changePassword']);
        
        // login user subscription
        Route::get('/user/subscription', [UserController::class, 'activeUserSubscription'])->middleware('check.role:2,4');
        Route::get('/user-rewards/list', [RewardController::class, 'userRewardList'])->middleware('check.role:2,4');
        Route::post('/user-rewards/status-update/{id}', [RewardController::class, 'userRewardRedeem']);

        // Update Profile
        Route::group(['prefix' => 'update-profile'], function () {
            Route::post('/pub-owner', [AuthController::class, 'pubOwnerProfileUpdate'])->middleware('check.role:2,4');
            Route::post('/sponsor', [AuthController::class, 'sponsorProfileUpdate'])->middleware('check.role:3');
            Route::post('/organizer', [AuthController::class, 'organizerProfileUpdate'])->middleware('check.role:4');
        });

        // Organizer
        Route::group(['middleware' => 'check.role:2,4', 'prefix' => 'organizer'], function () {
            Route::get('/list', [OrganizerController::class, 'index']);
            Route::get('/game-details/{id}', [OrganizerController::class, 'gameDetails']);
            Route::get('/details/{id}', [OrganizerController::class, 'show']);
            Route::post('/store', [OrganizerController::class, 'store']);
            Route::post('/update/{id}', [OrganizerController::class, 'update']);
            Route::post('/status-update/{id}', [OrganizerController::class, 'statusUpdate']);
            Route::delete('/delete/{id}', [OrganizerController::class, 'destroy']);
        });

        // Sponsor Request
        Route::group(['middleware' => 'check.role:2,4'], function () {
            Route::get('/sponsor-requests', [SponsorController::class, 'sponsorRequests']);
            Route::get('sponsorship/game/{id}', [SponsorController::class, 'gameSponsorships']);
            Route::post('sponsorship/status-update/{id}', [SponsorController::class, 'sponsorshipStatusUpdate']);
        });
        
        // Sponsorships 
        Route::group(['middleware' => 'check.role:3', 'prefix' => 'sponsorship'], function () {
            Route::post('/add', [SponsorController::class, 'addSponsorship']);
            Route::get('/details/{id}', [SponsorController::class, 'sponsorshipDetails']);
            Route::post('/update/{id}', [SponsorController::class, 'updateSponsorship']);
            Route::delete('/delete/{id}', [SponsorController::class, 'deleteSponsorship']);
            Route::get('/show', [SponsorController::class, 'showSponsorships']);
        });

        // Game
        Route::group(['prefix' => 'game'], function () {
            Route::post('/store', [GameController::class, 'gameStore'])->middleware('check.role:2,4');
            Route::get('/categories', [GameController::class, 'gameCategories']);
            Route::get('/details', [GameController::class, 'gameDetails']);
            Route::post('/already-exist', [GameController::class, 'gameAlreadyExist'])->middleware('check.role:2,4');
            Route::get('/coupon-questions', [GameController::class, 'couponQuestions']);
            Route::get('/show', [GameController::class, 'showGames'])->middleware('check.role:2,4');
            Route::get('/leaderboard/{id}', [GameController::class, 'gameLeaderboard'])->middleware('check.role:2,4');
            Route::get('/list/{id}', [GameController::class, 'gameList']);
            Route::get('/question-difficulty', [GameController::class, 'showGameQuestionDifficulty']);
            Route::post('/add/question-difficulty', [GameController::class, 'addGameQuestionDifficulty'])->middleware('check.role:1');
        });

        Route::get('/positions/list', [GameController::class, 'getAllPositions']);

        // Rewards
        Route::group(['prefix' => 'rewards'], function () {
            Route::get('/list', [RewardController::class, 'index']);
            Route::get('/all', [RewardController::class, 'allActive']);
            Route::post('/store', [RewardController::class, 'store']);
            Route::get('/show/{id}', [RewardController::class, 'show']);
            Route::get('/edit/{id}', [RewardController::class, 'edit']);
            Route::post('/update/{id}', [RewardController::class, 'update']);
            Route::delete('/delete/{id}', [RewardController::class, 'destroy']);
            Route::post('/status-update/{id}', [RewardController::class, 'statusUpdate']);
        });

        Route::group(['prefix' => 'game-type'], function () {
            Route::get('/list', [GameController::class, 'getGameTypes']);
        });

        // Sport Radar
        Route::group(['prefix' => 'sport-radar'], function () {
            Route::get('/seasons/list', [SportRadarController::class, 'getSeasons']);
            Route::get('/season/schedule/{seasonID}', [SportRadarController::class, 'getSeasonSchedule']);
        });

        Route::get('/sport-event/list', [SportEventController::class, 'list']);

        // App User
        Route::group(['prefix' => 'app-user'], function () {
            Route::get('/list', [UserController::class, 'index']);
            Route::get('/{id}', [UserController::class, 'appUser']);
            Route::post('/status-update/{id}', [UserController::class, 'appUserStatusUpdate']);
            // Route::get('/new', [UserController::class, 'newUsers']);
            // Route::get('/active', [UserController::class, 'activeUsers']);
        });

        // list of user subscription in admin
        Route::group(['middleware' => 'check.role:1', 'prefix' => 'user-subscription'], function () {
            Route::get('/list', [UserController::class, 'userSubscription']);
        });
        
        // Web User
        Route::group(['middleware' => 'check.role:1', 'prefix' => 'web-user'], function () {
            Route::get('/list/{id}', [UserController::class, 'webUserList']);
            Route::post('/status-update/{id}',[UserController::class,'webUserStatusUpdate']);
        });

        Route::group(['middleware' => 'check.role:1'], function () {
            Route::post('/add/pub-owner', [UserController::class, 'addPubOwner']);
        });

        Route::group(['middleware' => 'check.role:1', 'prefix' => 'demograph'], function () {
            Route::get('/{type}', [DemographicsController::class, 'getChart'])
            ->whereIn('type', ['getGendorGraph', 'getGamesPlayedGraph', 'getCountriesGraph']);
            Route::get('users/{type}',[DemographicsController::class, 'getUsers'])
            ->whereIn('type', ['active', 'new']);
        });

        // Pub (Home)
        Route::get('/pub/home',[GameController::class,'pubHome'])->middleware('check.role:2,4');
        Route::get('/home',[GameController::class,'adminHome'])->middleware('check.role:1');

        // Notification
        Route::group(['prefix' => 'notifications'], function () {
            Route::get('/new', [NotificationController::class, 'notificationNew']);
            Route::get('/list', [NotificationController::class, 'notificationList']);
            Route::post('/status-update/{id}', [NotificationController::class, 'notificationStatusUpdate']);
            Route::post('/bulk', [NotificationController::class, 'bulkNotificationStatusUpdate']);
        });

        Route::get('/logout', [AuthController::class, 'logout']);
    });

});

Route::get('event/lineup/{id}', [GameController::class, 'eventLineup']);
