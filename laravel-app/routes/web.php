<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\MlForecastController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\GoogleController;
use App\Http\Controllers\FacebookController;
use App\Http\Controllers\User\DashboardController as UserDashboardController;
use App\Http\Controllers\User\BookingController as UserBookingController;
use App\Http\Controllers\User\WaiverController as UserWaiverController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\ChatMessagingController;
use App\Http\Controllers\VisitorSummaryController;
use App\Http\Controllers\ZoneForecastController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\CmsController;
use App\Http\Controllers\BookingCmsController;
use App\Http\Controllers\EmailLoginController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\Admin\MyAccountController;
use App\Http\Controllers\Admin\SystemLogController;
use App\Http\Controllers\PromoAiController;


/*
|--------------------------------------------------------------------------
| ROLES
|   customer = online customer
|   cashier  = POS (inventory: view only)
|   tl       = team leader
|   manager  = manager
|   admin    = admin
|--------------------------------------------------------------------------
*/

/*
|--------------------------------------------------------------------------
| PUBLIC ROUTES (no login required)
|--------------------------------------------------------------------------
*/

// Landing page
Route::get('/', function () {
    return view('landing', [
        'hero'    => \App\Models\SiteContent::section('hero'),
        'split'   => \App\Models\SiteContent::section('split'),
        'contact' => \App\Models\SiteContent::section('contact'),
        'footer'  => \App\Models\SiteContent::section('footer'),

        'passes'      => \App\Models\SiteCard::type('pass')->active()->ordered()->get(),
        'attractions' => \App\Models\SiteCard::type('attraction')->active()->ordered()->get(),
        'services'    => \App\Models\SiteCard::type('service')->active()->ordered()->get(),
        'steps'       => \App\Models\SiteCard::type('step')->active()->ordered()->get(),
    ]);
})->name('home');

// Public employee QR check-in (protected by PIN/token, failed attempts are logged).
// Exempt from CSRF (bootstrap/app.php), so it is rate limited here: 10 tries per minute per IP
// to prevent the PIN from being guessed in rapid succession.
Route::get('/attendance/checkin', [AttendanceController::class, 'showCheckin'])->name('attendance.checkin');
Route::post('/attendance/checkin', [AttendanceController::class, 'storeCheckin'])
    ->middleware('throttle:10,1')
    ->name('attendance.checkin.store');

// Auth
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/logout', [AuthController::class, 'logout'])->name('logout');
Route::post('/logout', [AuthController::class, 'logout']);

// Email login (passwordless code)
Route::post('/login/email', [EmailLoginController::class, 'sendCode'])
    ->middleware('throttle:5,1')->name('login.email.send');
Route::get('/login/email/verify', [EmailLoginController::class, 'showVerifyForm'])
    ->name('login.email.verify.show');
Route::post('/login/email/verify', [EmailLoginController::class, 'verify'])
    ->middleware('throttle:10,1')->name('login.email.verify');
Route::post('/login/email/resend', [EmailLoginController::class, 'resend'])
    ->middleware('throttle:3,1')->name('login.email.resend');

// Two-Factor Authentication (email code)
Route::get('/two-factor', [AuthController::class, 'showTwoFactorForm'])->name('two-factor.show');
Route::post('/two-factor', [AuthController::class, 'verifyTwoFactor'])->name('two-factor.verify');
Route::post('/two-factor/resend', [AuthController::class, 'resendTwoFactor'])->name('two-factor.resend');

// Register
Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [AuthController::class, 'register']);

// Forgot / Reset Password (email code)
Route::get('/forgot-password', [AuthController::class, 'showForgotPasswordForm'])->name('forgot-password.show');
Route::post('/forgot-password', [AuthController::class, 'sendResetCode'])->name('forgot-password.send');
Route::get('/reset-password', [AuthController::class, 'showResetForm'])->name('reset-password.show');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('reset-password.update');
Route::post('/reset-password/resend', [AuthController::class, 'resendResetCode'])->name('reset-password.resend');

// Google OAuth
Route::get('/auth/google/redirect', [GoogleController::class, 'redirect'])->name('google.redirect');
Route::get('/auth/google/callback', [GoogleController::class, 'callback'])->name('google.callback');

// Facebook OAuth
Route::get('/auth/facebook/redirect', [FacebookController::class, 'redirect'])->name('facebook.redirect');
Route::get('/auth/facebook/callback', [FacebookController::class, 'callback'])->name('facebook.callback');

// TODO: check whether guests use this (landing chat widget?).
// If it is staff only, move it to the staff group below.
Route::resource('chatmessaging', ChatMessagingController::class);

/*
|--------------------------------------------------------------------------
| LOGGED IN (any role, including customers)
|--------------------------------------------------------------------------
*/
Route::middleware('auth.session')->group(function () {

    // My Account (profile photo, name, password). Previously had no middleware,
    // now requires a logged-in user.
    Route::get('/admin/my-account', [MyAccountController::class, 'index'])->name('my-account.index');
    Route::put('/admin/my-account', [MyAccountController::class, 'updateInfo'])->name('my-account.update');
    Route::put('/admin/my-account/password', [MyAccountController::class, 'updatePassword'])->name('my-account.password');

    // User app (customer-facing): Account, Waiver, Booking, My Bookings
    Route::prefix('app')->name('user.')->group(function () {
        Route::get('/dashboard', [UserDashboardController::class, 'index'])->name('dashboard');
        Route::post('/account', [UserDashboardController::class, 'update'])->name('account.update');
        Route::post('/account/two-factor', [UserDashboardController::class, 'toggleTwoFactor'])->name('account.two-factor.toggle');
        Route::post('/account/password', [UserDashboardController::class, 'requestPasswordChange'])->name('account.password.request');
        Route::post('/account/password/confirm', [UserDashboardController::class, 'confirmPasswordChange'])->name('account.password.confirm');
        Route::post('/account/password/cancel', [UserDashboardController::class, 'cancelPasswordChange'])->name('account.password.cancel');
        Route::post('/account/password/resend', [UserDashboardController::class, 'resendPasswordChangeCode'])->name('account.password.resend');

        Route::get('/waiver', [UserWaiverController::class, 'show'])->name('waiver');
        Route::post('/waiver', [UserWaiverController::class, 'store'])->name('waiver.store');
        Route::get('/bookings/{booking}/waiver', [UserWaiverController::class, 'show'])->name('bookings.waiver');
        Route::post('/bookings/{booking}/waiver', [UserWaiverController::class, 'store'])->name('bookings.waiver.store');

        Route::get('/booking', [UserBookingController::class, 'create'])->name('booking');
        Route::post('/booking', [UserBookingController::class, 'store'])->name('booking.store');
        Route::post('/booking/submit', [UserBookingController::class, 'submit'])->name('booking.submit');
        Route::get('/booking/{booking}/status', [UserBookingController::class, 'status'])->name('booking.status');

        Route::get('/my-bookings', [UserBookingController::class, 'index'])->name('bookings');
        Route::get('/activity', [\App\Http\Controllers\User\ActivityLogController::class, 'index'])->name('activity');

        Route::post('/bookings/{booking}/cancel', [UserBookingController::class, 'cancel'])->name('bookings.cancel');
        Route::post('/bookings/{booking}/reschedule', [UserBookingController::class, 'reschedule'])->name('bookings.reschedule');
        Route::get('/bookings/{booking}/review', [UserBookingController::class, 'review'])->name('bookings.review');
        Route::post('/bookings/{booking}/payment', [UserBookingController::class, 'confirmPayment'])->name('bookings.payment');
        Route::get('/bookings/{booking}/reschedule', [UserBookingController::class, 'editReschedule'])->name('bookings.reschedule.edit');
        Route::get('/bookings/{booking}/receipt', [UserBookingController::class, 'receipt'])->name('bookings.receipt');
    });

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

/*
|--------------------------------------------------------------------------
| STAFF: cashier, tl, manager, admin
|--------------------------------------------------------------------------
*/
// /home only redirects by role (customer -> /app/dashboard, cashier -> /inventory), so it must
// be open to every logged-in user, not just staff. Otherwise customers get a 403.
Route::get('/home', [HomeController::class, 'index'])->middleware('auth.session');

Route::middleware(['auth.session', 'role:cashier,tl,manager,admin'])->group(function () {


    // Inventory: VIEW ONLY page, open to cashiers.
    // All add/edit/delete/restock/import/export actions are in the LEADS group below.
    Route::get('/inventory', [InventoryController::class, 'index']);

    // POS
    Route::get('/pos', [PosController::class, 'index']);
    Route::get('/pos/get_cashflow', [PosController::class, 'getCashflow']);
    Route::get('/pos/open-drawer', [PosController::class, 'openDrawer']);
    Route::get('/pos/get_transaction', [PosController::class, 'getTransaction']);
    Route::post('/pos/verify_manager', [PosController::class, 'verifyManager']);
    Route::post('/pos/save-transaction', [PosController::class, 'saveTransaction']);
    Route::post('/pos/log-void', [PosController::class, 'logVoid']);
    Route::post('/pos/void-transaction', [PosController::class, 'voidTransaction']);
    Route::get('/pos/voucher-lookup', [PosController::class, 'lookupVoucher']);
    Route::post('/pos/reprint', [PosController::class, 'reprintTransaction'])->name('pos.reprint');
    Route::post('/pos/cash-movement', [PosController::class, 'addCashMovement']);
    Route::post('/pos/print-sales-report', [PosController::class, 'printSalesReportData']);
    Route::get('/pos/present-report', [PosController::class, 'presentReport']);
    Route::get('/pos/historical-cutoffs', [PosController::class, 'historicalCutoffs']);
    Route::get('/pos/cutoff/{id}', [PosController::class, 'cutoffDetail']);
    Route::get('/pos/cutoff-status', [PosController::class, 'cutoffStatus']);

     Route::post('/ai/promo-suggest', [PromoAiController::class, 'suggest'])->name('ai.promo-suggest');

    // Notifications (fixed URLs must come before {notification})
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/poll', [NotificationController::class, 'poll'])->name('notifications.poll');
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllRead'])->name('notifications.mark-all-read');
    Route::patch('/notifications/bulk-read', [NotificationController::class, 'bulkMarkRead'])->name('notifications.bulk-read');
    Route::delete('/notifications/bulk-destroy', [NotificationController::class, 'bulkDestroy'])->name('notifications.bulk-destroy');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
    Route::delete('/notifications/{notification}', [NotificationController::class, 'destroy'])->name('notifications.destroy');
});

/*
|--------------------------------------------------------------------------
| LEADS: tl, manager, admin
| (includes tl because /ml-forecast is their landing page after login)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth.session', 'role:tl,manager,admin'])->group(function () {

    // Inventory: editing actions (NOT allowed for cashiers)
    Route::post('/inventory/add', [InventoryController::class, 'store']);
    Route::post('/inventory/update', [InventoryController::class, 'update']);
    Route::post('/inventory/delete', [InventoryController::class, 'destroy']);
    Route::post('/inventory/restock', [InventoryController::class, 'restock']);
    Route::post('/inventory/import', [InventoryController::class, 'import']);
    Route::post('/inventory/export', [InventoryController::class, 'export'])->name('inventory.export');
    Route::post('/inventory/add-category', [InventoryController::class, 'storeCategory'])->name('inventory.addCategory');

    // Reservations
    Route::get('/reservations', [ReservationController::class, 'index'])->name('reservations.index');
    Route::get('/reservations/{reservation}', [ReservationController::class, 'show'])->name('reservations.show');
    Route::post('/reservations', [ReservationController::class, 'store'])->name('reservations.store');
    Route::put('/reservations/{reservation}', [ReservationController::class, 'update'])->name('reservations.update');
    Route::patch('/reservations/{reservation}/status', [ReservationController::class, 'updateStatus'])->name('reservations.status');
    Route::post('/reservations/{reservation}/approve-payment', [ReservationController::class, 'approvePayment'])->name('reservations.approve-payment');
    Route::post('/reservations/{reservation}/reject-payment', [ReservationController::class, 'rejectPayment'])->name('reservations.reject-payment');
    Route::delete('/reservations/{reservation}', [ReservationController::class, 'destroy'])->name('reservations.destroy');

    // Customers / Visitors
    Route::get('/customer', [VisitorSummaryController::class, 'index'])->name('visitor-summary');
    Route::get('/analytics/live-count', [VisitorSummaryController::class, 'liveCount'])->name('analytics.live-count');

    // AI Promo Generator (FIX: nasa loob na ng auth + role group; dati nasa pinakataas na walang proteksyon)
    Route::post('/ai/promo-suggest', [PromoAiController::class, 'suggest'])->name('ai.promo-suggest');

    // Analytics / ML Forecast
    Route::get('/analytics', [AnalyticsController::class, 'index']);
    // (duplicate unnamed /ml-forecast route removed; the named one below is the one that was already active)

    Route::get('/api/ml/summary', [MlForecastController::class, 'summary']);
    Route::get('/api/ml/top_products', [MlForecastController::class, 'topProducts']);
    Route::get('/api/ml/day_of_week', [MlForecastController::class, 'dayOfWeek']);
    Route::get('/api/ml/hourly_peaks', [MlForecastController::class, 'hourlyPeaks']);
    Route::get('/api/ml/next_month_forecast', [MlForecastController::class, 'nextMonthForecast']);
    Route::get('/api/ml/monthly_trend', [MlForecastController::class, 'monthlyTrend']);
    Route::get('/api/ml/report', [MlForecastController::class, 'downloadReport']);
    Route::get('/api/ml/voucher_summary', [MlForecastController::class, 'voucherSummary']);
    Route::get('/api/zone-forecast/{zone}/report', [ZoneForecastController::class, 'downloadReport']);
    Route::get('/api/zone-forecast/{zone}/voucher_summary', [ZoneForecastController::class, 'voucherSummary']);
    Route::get('/ml-forecast', [MlForecastController::class, 'index'])->name('ml.forecast');
    Route::get('/api/ml/zone-summary', [MlForecastController::class, 'zoneSummary']);

    // Zone Forecast pages
    // URLs stay the same so sidebar links keep working. The 'zone' value MUST match
    // categories.zone exactly (renamed by the zone-rename migration).
    Route::get('/fieldOfRides', [ZoneForecastController::class, 'index'])
        ->defaults('zone', 'Piyestarides')->name('zone.fieldOfRides');
    Route::get('/RollerFever', [ZoneForecastController::class, 'index'])
        ->defaults('zone', 'Skataya')->name('zone.rollerFever');
    Route::get('/DinoAdventure', [ZoneForecastController::class, 'index'])
        ->defaults('zone', 'Dinolakbay')->name('zone.dinoAdventure');
});

/*
|--------------------------------------------------------------------------
| MANAGEMENT: manager, admin
| Attendance, Payroll, Employees, Reports, CMS (including pricing)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth.session', 'role:manager,admin'])->group(function () {

    // Reports
    Route::get('/reports/present', [AnalyticsController::class, 'reportPresent']);
    Route::get('/reports/historical', [AnalyticsController::class, 'reportHistorical']);
    Route::get('/reports/bir', [AnalyticsController::class, 'reportBir']);

    Route::get('/Snackbar', fn () => app(ZoneForecastController::class)->index('Snackbar'));

    // Attendance / Payroll
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance');
    Route::get('/attendance/live', [AttendanceController::class, 'liveSignature'])->name('attendance.live');
    Route::get('/attendance/salary', [AttendanceController::class, 'salary'])->name('salary');
    Route::get('/attendance/payroll-history', [AttendanceController::class, 'payrollHistory'])->name('payroll-history');
    Route::get('/attendance/payslip', [AttendanceController::class, 'payslip'])->name('payslip');
    Route::get('/attendance/payslip/{id}', [AttendanceController::class, 'payslip'])->name('payslip.show');
    Route::post('/attendance/salary/rates', [AttendanceController::class, 'updateRates'])->name('salary.rates');
    Route::post('/attendance/salary/store', [AttendanceController::class, 'storePayroll'])->name('salary.store');
    Route::post('/attendance/store', [AttendanceController::class, 'storeAttendance'])->name('attendance.store');
    Route::post('/attendance/import', [AttendanceController::class, 'importAttendance'])->name('attendance.import');
    Route::post('/attendance/time-in', [AttendanceController::class, 'timeIn']);
    Route::post('/attendance/time-out', [AttendanceController::class, 'timeOut']);
    Route::post('/attendance/qr/regenerate', [AttendanceController::class, 'regenerateQr'])->name('attendance.qr.regenerate');

    // Employees
    Route::get('/employees', [EmployeeController::class, 'index'])->name('employees.index');
    Route::post('/employees', [EmployeeController::class, 'store'])->name('employees.store');
    Route::post('/employees/import', [EmployeeController::class, 'import'])->name('employees.import');
    Route::put('/employees/{employee}', [EmployeeController::class, 'update'])->name('employees.update');
    Route::post('/employees/{employee}/deactivate', [EmployeeController::class, 'deactivate'])->name('employees.deactivate');
    Route::post('/employees/{employee}/activate', [EmployeeController::class, 'activate'])->name('employees.activate');

    // CMS
    Route::prefix('admin/cms')->name('cms.')->group(function () {

        Route::get('/', [CmsController::class, 'index'])->name('index');

        // Simple text sections: hero, split, contact, footer
        Route::get('/section/{section}', [CmsController::class, 'editSection'])->name('section.edit');
        Route::put('/section/{section}', [CmsController::class, 'updateSection'])->name('section.update');

        Route::post('/collections', [CmsController::class, 'storeCollection'])->name('collections.store');
        Route::delete('/collections/{collection:slug}', [CmsController::class, 'destroyCollection'])->name('collections.destroy');

        // Booking & Pricing
        Route::get('/booking', [BookingCmsController::class, 'edit'])->name('booking.pricing');
        Route::put('/booking', [BookingCmsController::class, 'update'])->name('booking.pricing.update');
        Route::post('/booking/package/{service}/{package}/reset', [BookingCmsController::class, 'resetPackage'])->name('booking.pricing.reset-package');
        Route::post('/booking/addon/{service}/{addon}/reset', [BookingCmsController::class, 'resetAddon'])->name('booking.pricing.reset-addon');

        // Card-based sections: pass, attraction, service, step
        Route::get('/cards/{type}', [CmsController::class, 'cards'])->name('cards');
        Route::get('/cards/{type}/create', [CmsController::class, 'createCard'])->name('cards.create');
        Route::post('/cards/{type}', [CmsController::class, 'storeCard'])->name('cards.store');
        Route::get('/card/{card}/edit', [CmsController::class, 'editCard'])->name('cards.edit');
        Route::put('/card/{card}', [CmsController::class, 'updateCard'])->name('cards.update');
        Route::delete('/card/{card}', [CmsController::class, 'destroyCard'])->name('cards.destroy');
    });
});

/*
|--------------------------------------------------------------------------
| ADMIN ONLY
| Accounts, System Logs, and destructive actions (delete payroll batches,
| attendance, employees). If you want managers to be able to delete payroll
| batches, move those routes to the manager,admin group above.
|--------------------------------------------------------------------------
*/
Route::middleware(['auth.session', 'role:admin'])->group(function () {

    // System logs
    Route::get('/admin/system-logs', [SystemLogController::class, 'index'])->name('system-logs.index');
    // "Yes, it's me" / "No, it's not me" review for failed logins
    Route::post('/admin/system-logs/{id}/review', [SystemLogController::class, 'review'])->name('system-logs.review');

    // Accounts
    Route::get('/accounts', [AccountController::class, 'index']);
    Route::get('/accounts/create', [AccountController::class, 'create']);
    Route::post('/accounts/store', [AccountController::class, 'store']);
    Route::put('/accounts/update/{id}', [AccountController::class, 'update']);
    Route::delete('/accounts/delete/{id}', [AccountController::class, 'destroy']);

    // Payroll batch delete (two URLs, same controller method)
    Route::delete('/attendance/payroll-history/{batch}', [AttendanceController::class, 'deleteBatch']);
    Route::delete('/payroll-history/bulk-delete', [AttendanceController::class, 'bulkDeleteBatches'])->name('payroll-history.bulkDelete');
    Route::delete('/payroll-history/{batch}', [AttendanceController::class, 'deleteBatch'])->name('payroll-history.delete-batch');

    // Delete everything
    Route::delete('/attendance/clear-all', [AttendanceController::class, 'clearAll'])->name('attendance.clear-all');
    Route::delete('/employees/clear-all', [EmployeeController::class, 'clearAll'])->name('employees.clear-all');
});