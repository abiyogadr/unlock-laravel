<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CertificateController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Admin\{
    AdminDashboardController,
    AdminEcourseController,
    AdminEventController,
    AdminPacketController,
    AdminRegistrationController,
    AdminSpeakerController
};
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\MidtransController;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use App\Http\Controllers\ToolsController;
use App\Http\Controllers\Ecourse\EcourseController;
use App\Http\Controllers\Ecourse\CourseController;

// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/', [HomeController::class, 'index'])->name('home');

/**EVENT */
Route::get('/events', [EventController::class, 'index'])
    ->name('event.index');
Route::get('/event/{event_code}', [EventController::class, 'show'])
    ->name('event.show');

/**CERTIFICATE */
// Halaman form input ID
// Route::get('/certificate', [CertificateController::class, 'index'])->name('certificate');

// // Proses verifikasi ID
// Route::post('/verify-certificate', [CertificateController::class, 'verify'])->name('certificate.verify');

// // // Halaman view sertifikat
// Route::get('/certificate/{id}', [CertificateController::class, 'show'])->name('certificate.view');

Route::prefix('certificate')->name('certificate.')->group(function () {
    
    // Halaman Form Cari (GET: /certificate)
    Route::get('/', [CertificateController::class, 'index'])->name('index');

    // Proses Verifikasi Form (POST: /certificate/verify)
    Route::post('/verify', [CertificateController::class, 'verify'])->name('verify');

    // Halaman Tampilan Sertifikat (GET: /certificate/{id})
    Route::get('/{id}', [CertificateController::class, 'show'])->name('view');

});

// Alias agar route('certificate') tetap bekerja jika Anda memanggilnya di navigasi
Route::get('/certificate', [CertificateController::class, 'index'])->name('certificate');

//**REGISTRATION */
// Route Public (Tanpa Login)
Route::withoutMiddleware([VerifyCsrfToken::class])
    ->post('/midtrans/callback', [MidtransController::class, 'callback'])
    ->name('midtrans.callback');

// Endpoint AJAX (Biasanya public untuk form registrasi)
Route::get('/registration/regencies/{provinceCode}', [RegistrationController::class, 'getRegencies']);
Route::get('/registration/packets/{event}', [RegistrationController::class, 'getPackets']);

// Route yang WAJIB Login
Route::middleware(['auth'])->group(function () {
    
    // --- REGISTRATION FLOW ---
    Route::get('/registration', [RegistrationController::class, 'create'])->name('registration.create');
    Route::post('/registration', [RegistrationController::class, 'store'])->name('registration.store');
    Route::post('/registration/apply-voucher', [RegistrationController::class, 'applyEventVoucher'])->name('registration.applyVoucher');
    Route::get('/registration/status/{status}', [RegistrationController::class, 'status'])->name('registration.status');
    Route::get('/registration/payment/{registration}', [RegistrationController::class, 'payment'])->name('registration.payment');

    // --- ATTENDANCE SYSTEM ---
    // List event yang belum diabsen (Halaman Utama /attendance)
    Route::get('/attendance', [RegistrationController::class, 'listAttendance'])
        ->name('registration.attendance.list');

    // Form Pengisian Absensi
    Route::get('/attendance/{registration_code}', [RegistrationController::class, 'attendance'])
        ->name('registration.attendance');

    // Proses Simpan Absensi
    Route::post('/attendance/store/{id}', [RegistrationController::class, 'storeAttendance'])
        ->name('registration.attendance.store');
        
});

// Route login (tidak pakai middleware)
Route::get('login', [AuthController::class, 'show'])->name('login');
Route::post('login', [AuthController::class, 'login']);
Route::post('logout', [AuthController::class, 'logout'])->name('logout');

/**AUTH */
// Middleware 'guest' mencegah user yang sudah login masuk ke halaman login lagi
Route::get('/login', [AuthController::class, 'show'])->middleware('guest')->name('login');
Route::post('/login', [AuthController::class, 'login'])->middleware('guest')->name('login.process');
Route::get('/auth/google', [GoogleAuthController::class, 'redirectToGoogle'])->name('auth.google');
Route::get('/signup', [AuthController::class, 'showSignUpForm'])->middleware('guest')->name('signup.create');
Route::post('/signup', [AuthController::class, 'signup'])->middleware('guest')->name('signup.store');
// Halaman pemberitahuan untuk verifikasi email
Route::get('/email/verify', function () {return view('auth.verify-email');})->middleware('auth')->name('verification.notice');

// Handler klik link verifikasi dari email
Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();

    // Mengambil tujuan redirect dari session, jika tidak ada default ke /home
    $redirectTo = session()->pull('url.intended', '/');

    return redirect($redirectTo)->with('verified', true);
})->middleware(['auth', 'signed'])->name('verification.verify');

// Kirim ulang link verifikasi
Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();
    return redirect()->route('verification.notice')
        ->with('message', 'Link verifikasi baru telah dikirim!');
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');

Route::get('/cities', [AuthController::class, 'getCities'])->middleware('auth')->name('cities');
// 1. Tampilkan Form Lupa Password
Route::get('forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])
    ->name('password.request');

// 2. Kirim Link Reset ke Email
Route::post('forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])
    ->name('password.email');

// 3. Tampilkan Form Reset Password (Link dari Email)
Route::get('reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])
    ->name('password.reset');

// 4. Proses Simpan Password Baru
Route::post('reset-password', [ResetPasswordController::class, 'reset'])
    ->name('password.update');

Route::get('/auth/google/callback', [GoogleAuthController::class, 'handleGoogleCallback']);
// Logout Route
// Middleware 'auth' memastikan hanya yang sudah login yang bisa logout
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');


Route::prefix('upanel')->middleware(['auth', 'admin'])->name('admin.')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/ecourse', [AdminEcourseController::class, 'index'])->name('ecourse.index');
    Route::get('/ecourse/data', [AdminEcourseController::class, 'data'])->name('ecourse.data');
    Route::get('/ecourse/summary', [AdminEcourseController::class, 'summary'])->name('ecourse.summary');
    Route::get('/ecourse/export', [AdminEcourseController::class, 'export'])->name('ecourse.export');
    Route::get('/ecourse/transactions/{transaction}', [AdminEcourseController::class, 'show'])->name('ecourse.show');
    
    Route::resource('events', AdminEventController::class);
    Route::resource('packets', AdminPacketController::class);
    Route::resource('speakers', AdminSpeakerController::class);
    Route::resource('registrations', AdminRegistrationController::class);
    Route::get('/registrations/event/{event}', [AdminRegistrationController::class, 'showEventRegistrants'])->name('registrations.show_event');
    Route::patch('/registrations/{registration}/verify', [AdminRegistrationController::class, 'verify'])->name('registrations.verify');
    Route::patch('/registrations/{registration}/reject', [AdminRegistrationController::class, 'reject'])->name('registrations.reject');
    Route::get('/registrations/event/{event}/list-certificate', [AdminRegistrationController::class, 'listForCertificate'])->name('registrations.list_for_certificate');
    Route::get('/registrations/{registration}/generate-certificate', [AdminRegistrationController::class, 'generateSingleCertificate'])->name('registrations.generate_certificate');
    Route::post('/registrations/generate-mass-certificate', [AdminRegistrationController::class, 'generateMassCertificate'])->name('registrations.generate_mass_certificate');
    Route::get('/registrations/export/{event_id}', [AdminRegistrationController::class, 'exportCsv'])->name('registrations.export');
    Route::patch('/events/{event}/toggle-attendance', [AdminEventController::class, 'toggleAttendance'])->name('events.toggle_attendance');
    Route::get('/events/{event}/certificate-setup', [AdminRegistrationController::class, 'setupCertificate'])->name('events.certificate.setup');
    Route::patch('/events/{event}/certificate-setup', [AdminRegistrationController::class, 'storeCertificateSetup'])->name('events.certificate.store');
    Route::post('/admin/events/{event}/certificate/preview', [AdminRegistrationController::class, 'previewCertificate'])->name('events.certificate.preview');
});

/** USER ROUTE */
Route::middleware(['auth'])->group(function () {
    Route::get('/myevents', [UserController::class, 'myevents'])
        ->name('myevents');
    Route::get('/myevents/{id}', [UserController::class, 'show'])
        ->name('myevents.show');
    Route::get('/profile', [UserController::class, 'profile'])
        ->name('profile');
    Route::post('/profile', [UserController::class, 'profileUpdate'])
        ->name('profile.update');
});

/**PAGES */
Route::get('/faq', function () {return view('pages.faq');})->name('faq');
Route::get('/about', function () {return view('pages.about');})->name('about');
Route::get('/policy', function () {return view('pages.policy');})->name('policy');

/**E-COURSE */
// Load E-Course Inertia routes
require base_path('routes/ecourse-inertia.php');

/**LANDING PAGE */
require base_path('routes/landing-page.php');