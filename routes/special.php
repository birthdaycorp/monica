<?php

use Illuminate\Support\Facades\Route;

Route::get('/settings/emailchange1', 'Auth\EmailChangeController@showLoginFormSpecial');
Route::get('/auth/login-recovery', 'Auth\RecoveryLoginController@get')->name('recovery.login');

Route::middleware('throttle:30,1')->group(function () {
    Route::post('/settings/emailchange1', 'Auth\EmailChangeController@login');
    Route::post('/auth/login-recovery', 'Auth\RecoveryLoginController@store');    
});

Route::middleware(['auth', '2fa', 'throttle:5,1'])->group(function () {
    Route::get('/settings/emailchange2', 'Auth\EmailChangeController@index');
    Route::post('/settings/emailchange2', 'Auth\EmailChangeController@save');
});
