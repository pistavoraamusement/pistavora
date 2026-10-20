<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CmsCollection extends Model
{
    protected $fillable = [
        'slug', 'title', 'description', 'icon', 'unit_label', 'sort_order', 'is_system',
    ];

    protected $casts = [
        'is_system' => 'boolean',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}