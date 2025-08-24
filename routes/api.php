<?php

use App\Http\Controllers\Auth\InvitationController;
use App\Http\Controllers\Auth\MagicLinkController;
use App\Http\Controllers\OtpController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WellbeingPillarController;
use App\Http\Controllers\WellnessInterestController;
use Illuminate\Support\Facades\Route;

Route::post('/invite', [InvitationController::class, 'store']);

Route::get('/magic-link/user', [MagicLinkController::class, 'show'])
    ->name('api.magic-link.user')
    ->middleware('signed');

Route::controller(OtpController::class)->group(function () {
    Route::get('/verify-email', 'verifyEmail');   
    Route::get('/send-otp', 'sendEmailOtp');     
    Route::get('/verify-otp', 'verifyEmailOtp');
});

//Authenticate route
Route::middleware('auth:api')->group(function () {
    Route::put('/user/profile', [ProfileController::class, 'update']);
         
        // Wellbeing interest
        Route::get('/wellness-interests', [WellnessInterestController::class, 'index']);
        Route::post('/wellness-interests', [WellnessInterestController::class, 'store']);

        // Wellbeing Pillars
        Route::get('/wellbeing-pillars', [WellbeingPillarController::class, 'index']);
        Route::post('/wellbeing-pillars', [WellbeingPillarController::class, 'store']);
});