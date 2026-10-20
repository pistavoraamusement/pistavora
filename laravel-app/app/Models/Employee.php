<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class Employee extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'employee_name',
        'category',
        'area',          // Field of Rides / Dino Adventure / Roller Skates (Manager = null)
        'pin_code',
        'qr_token',
        'date_hired',
        'date_ended',
        'status',
    ];

    protected $hidden = [
        'pin_code',
    ];

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class, 'employee_id', 'id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }
}