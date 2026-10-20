<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteVisit extends Model
{
    protected $fillable = ['session_id', 'user_id', 'ip_address', 'last_seen_at'];

    protected $casts = ['last_seen_at' => 'datetime'];
}