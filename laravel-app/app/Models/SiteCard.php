<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class SiteCard extends Model
{
    protected $fillable = [
        'type', 'slug', 'badge_label', 'icon', 'title', 'description',
        'price_display', 'image_path', 'button_text', 'button_link',
        'features', 'modal_list', 'is_featured', 'sort_order', 'is_active',
         'badge_color', 'icon_type', 'icon_image_path',
    ];

    protected $casts = [
        'features'    => 'array',
        'modal_list'  => 'array',
        'is_featured' => 'boolean',
        'is_active'   => 'boolean',
    ];

    public function scopeType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order');
    }

    /**
     * Human-friendly labels for each card type, used across the CMS views.
     */
    public static function typeLabel(string $type): string
    {
        return match ($type) {
            'pass'       => 'Day Pass',
            'attraction' => 'Attraction',
            'service'    => 'Guest Service',
            'step'       => 'How-It-Works Step',
            default      => ucfirst($type),
        };
    }
}