<?php
 
use App\Http\Controllers\MlForecastController;
use App\Http\Controllers\ChatController;
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
 