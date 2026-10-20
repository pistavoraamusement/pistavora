<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalaryRate extends Model
{
    protected $table = 'salary_rates';

    protected $fillable = [
        'position',
        'daily_rate',
    ];
}