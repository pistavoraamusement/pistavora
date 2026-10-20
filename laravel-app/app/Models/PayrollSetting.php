<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayrollSetting extends Model
{
    protected $table = 'payroll_settings';

    protected $fillable = [
        'ot_multiplier',
        'holiday_ot_multiplier',
        'minutes_per_day',
        'late_rate_multiplier',
    ];

    // May iisang row lang ang table na ito — kunin ito, o gawa kung wala
    // pa, para hindi kailangan mag-null-check sa buong app.
    public static function current(): self
    {
        return static::first() ?? static::create([
            'ot_multiplier' => 1.25,
            'holiday_ot_multiplier' => 2.60,
            'minutes_per_day' => 480,
            'late_rate_multiplier' => 1.00,
        ]);
    }
}