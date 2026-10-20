<?php

use App\Http\Controllers\MlForecastController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\ZoneForecastController;
use Illuminate\Support\Facades\Route;

Route::prefix('ml')->group(function () {
    Route::get('/summary', [MlForecastController::class, 'summary']);
    Route::get('/top_products', [MlForecastController::class, 'topProducts']);
    Route::get('/day_of_week', [MlForecastController::class, 'dayOfWeek']);
    Route::get('/hourly_peaks', [MlForecastController::class, 'hourlyPeaks']);
    Route::get('/next_month_forecast', [MlForecastController::class, 'nextMonthForecast']);
    Route::get('/monthly_trend', [MlForecastController::class, 'monthlyTrend']);
    Route::get('/payment_breakdown', [MlForecastController::class, 'paymentBreakdown']);
    Route::get('/category_breakdown', [MlForecastController::class, 'categoryBreakdown']);
});

Route::post('/chat/message', [ChatController::class, 'message']);

// Zone Forecast API
Route::prefix('zone-forecast/{zone}')->group(function () {
    Route::get('/summary',              [ZoneForecastController::class, 'summary']);
    Route::get('/top_products',         [ZoneForecastController::class, 'topProducts']);
    Route::get('/day_of_week',          [ZoneForecastController::class, 'dayOfWeek']);
    Route::get('/hourly_peaks',         [ZoneForecastController::class, 'hourlyPeaks']);
    Route::get('/next_month_forecast',  [ZoneForecastController::class, 'nextMonthForecast']);
    Route::get('/monthly_trend',        [ZoneForecastController::class, 'monthlyTrend']);
    Route::get('/payment_breakdown',    [ZoneForecastController::class, 'paymentBreakdown']);
    Route::get('/category_breakdown',   [ZoneForecastController::class, 'categoryBreakdown']);
    Route::get('/voucher_summary',      [ZoneForecastController::class, 'voucherSummary']);   // NEW
    Route::get('/report',               [ZoneForecastController::class, 'downloadReport']);   // NEW
});