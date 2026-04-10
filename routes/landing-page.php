<?php

use App\Http\Controllers\Admin\AdminLandingPageController;
use App\Http\Controllers\LandingPagePublicController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Landing Page Routes
|--------------------------------------------------------------------------
*/

// ── Public Routes ────────────────────────────────────────────
Route::get('/p/{slug}', [LandingPagePublicController::class, 'show'])
    ->name('landing-page.show');

Route::get('/l/{link}', [LandingPagePublicController::class, 'trackClick'])
    ->name('landing-page.track');

// ── Admin Routes ─────────────────────────────────────────────
Route::prefix('upanel/landing-pages')
    ->middleware(['auth', 'admin'])
    ->name('admin.landing-pages.')
    ->group(function () {

        // Inertia builder page
        Route::get('/', [AdminLandingPageController::class, 'index'])
            ->name('index');

        Route::get('/{landing_page}/preview', [AdminLandingPageController::class, 'preview'])
            ->name('preview');

        // JSON API
        Route::post('/', [AdminLandingPageController::class, 'store'])
            ->name('store');

        Route::put('/{landing_page}', [AdminLandingPageController::class, 'update'])
            ->name('update');

        Route::delete('/{landing_page}', [AdminLandingPageController::class, 'destroy'])
            ->name('destroy');

        Route::patch('/{landing_page}/toggle-status', [AdminLandingPageController::class, 'toggleStatus'])
            ->name('toggle-status');

        Route::post('/{landing_page}/duplicate', [AdminLandingPageController::class, 'duplicate'])
            ->name('duplicate');

        Route::post('/{landing_page}/upload', [AdminLandingPageController::class, 'uploadImage'])
            ->name('upload');

        Route::post('/{landing_page}/remove-image', [AdminLandingPageController::class, 'removeImage'])
            ->name('remove-image');
    });
