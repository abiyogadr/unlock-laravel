<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdsRegistrationController;

/*
|--------------------------------------------------------------------------
| Ads Landing Page Routes
|--------------------------------------------------------------------------
*/

Route::prefix('ad')->name('ads.')->group(function () {

    Route::get('/bootcamp-coretax', function () {
        return view('ads.bootcamp-coretax');
    })->name('bootcamp-coretax');

    // Endpoint AJAX submit pendaftaran dari landing page
    // Tidak perlu login — tamu menggunakan user_id = 1
    Route::post('/register', [AdsRegistrationController::class, 'store'])->name('register');

    // Halaman pembayaran khusus ads (tidak perlu login)
    Route::get('/payment/{registration}', [AdsRegistrationController::class, 'payment'])->name('payment');

});
