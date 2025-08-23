<?php

use App\Http\Controllers\Auth\InvitationController;
use App\Http\Controllers\Auth\MagicLinkController;
use Illuminate\Support\Facades\Route;

Route::post('/invite', [InvitationController::class, 'store']);

Route::get('/magic-link/user', [MagicLinkController::class, 'show'])
    ->name('api.magic-link.user')
    ->middleware('signed');