<?php

use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::view('/me', 'profile.show')->name('profile.show');
});

require __DIR__.'/auth.php'; // genere par Breeze (login, register, password reset, verification email)
