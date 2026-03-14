<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Ecourse\EcourseInertiaController;
use App\Http\Controllers\Ecourse\CourseInertiaController;
use App\Http\Controllers\Ecourse\PaymentInertiaController;

// Public E-Course catalog (accessible without auth)
Route::get('/ecourse/catalog', [CourseInertiaController::class, 'catalog'])->name('ecourse.catalog');

// Course show (public - viewable without login)
Route::get('/ecourse/course/{course:slug}', [CourseInertiaController::class, 'show'])->name('ecourse.course.show');

Route::middleware(['auth'])->prefix('ecourse')->name('ecourse.')->group(function () {
    
    /**
     * DASHBOARD ROUTE
     */
    Route::get('/dashboard', [EcourseInertiaController::class, 'dashboard'])
        ->name('dashboard');

    /**
     * COURSE SHOW ROUTE
     * Moved to public routes above so the course page is viewable without authentication
     */

    /**
     * MY JOURNEY ROUTE
     */
    Route::get('/my-journey', [EcourseInertiaController::class, 'myJourney'])
        ->name('my-journey');

    /**
     * PAYMENT ROUTE
     */
    Route::get('/payment', [PaymentInertiaController::class, 'show'])
        ->name('payment');

    /**
     * PLAYER ROUTE
     */
    Route::get('/player/{course:slug}/{module:slug}', [EcourseInertiaController::class, 'player'])
        ->name('player');

    /**
     * CERTIFICATES ROUTE
     */
    Route::get('/certificates', [EcourseInertiaController::class, 'certificates'])
        ->name('certificates');

    // Course certificate viewing is handled by centralized CertificateController (Blade view).

    /**
     * API ROUTES (Integrated)
     */
    Route::prefix('api')->group(function () {
        // Module Actions
        Route::post('/module/{module:id}/complete', [EcourseInertiaController::class, 'completeModule'])->name('api.module.complete');
        Route::post('/module/{module:id}/progress', [EcourseInertiaController::class, 'updateProgress'])->name('api.module.progress');
        Route::post('/module/{module:id}/comment', [EcourseInertiaController::class, 'addComment'])->name('api.module.comment');
        Route::get('/module/{module:id}/material/{material:id}/download', [EcourseInertiaController::class, 'downloadMaterial'])->name('api.module.material.download');
        
        // Certificates
        Route::get('/certificate/{certificate:id}/view', [EcourseInertiaController::class, 'viewCertificate'])->name('api.certificate.view');

        // Get certificate for a specific course (if issued to authenticated user)
        Route::get('/course/{course:id}/certificate', [EcourseInertiaController::class, 'getCourseCertificate'])->name('api.course.certificate');

        // Get certificate status (includes completion, subscription, remaining quota)
        Route::get('/course/{course:id}/certificate/status', [EcourseInertiaController::class, 'courseCertificateStatus'])->name('api.course.certificate.status');

        // Attempt to generate a certificate (consumes quota). Returns created certificate on success.
        Route::post('/course/{course:id}/certificate/generate', [EcourseInertiaController::class, 'generateCourseCertificate'])->name('api.course.certificate.generate');
        
        // Payments - Checkout via PaymentInertiaController
        Route::post('/payments/checkout', [PaymentInertiaController::class, 'checkout'])->name('api.payments.checkout');
        // Voucher endpoints
        Route::post('/payments/apply-voucher', [PaymentInertiaController::class, 'applyVoucher'])->name('api.payments.applyVoucher');
        Route::post('/payments/remove-voucher', [PaymentInertiaController::class, 'removeVoucher'])->name('api.payments.removeVoucher');
        // Update transaction status endpoint (used to mark failed or cancel when necessary)
        Route::post('/payments/update-status', [PaymentInertiaController::class, 'updateStatus'])->name('api.payments.updateStatus');
        // API-prefixed alias used by frontend
        Route::post('/api/payments/update-status', [PaymentInertiaController::class, 'updateStatus']);

        // Get transaction status (used by frontend polling)
        Route::get('/payments/{order_id}/status', [PaymentInertiaController::class, 'getStatus'])->name('api.payments.getStatus');
        // API-prefixed alias used by frontend
        Route::get('/api/payments/{order_id}/status', [PaymentInertiaController::class, 'getStatus']);

        // Ensure user_course entry and current module for starting learning
        Route::post('/user-courses/{course:id}/ensure', [EcourseInertiaController::class, 'ensureUserCourse'])->name('api.usercourses.ensure');
    });

});

// Get recent incomplete modules endpoint
Route::get('/ecourse/notifications/recent-incomplete', [EcourseInertiaController::class, 'getRecentIncomplete'])
    ->middleware(['auth'])
    ->name('ecourse.notifications.recent');
