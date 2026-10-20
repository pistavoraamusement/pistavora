<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SiteContent extends Model
{
    protected $fillable = ['section', 'field_key', 'value'];

    /**
     * Get a single field value.
     * Example: SiteContent::get('hero', 'title', 'Default text')
     */
    public static function get(string $section, string $key, ?string $default = null): ?string
    {
        return Cache::rememberForever("site_content_{$section}_{$key}", function () use ($section, $key, $default) {
            return static::where('section', $section)
                ->where('field_key', $key)
                ->value('value') ?? $default;
        });
    }

    /**
     * Get all fields of a section as an associative array.
     * Example: SiteContent::section('hero') -> ['title' => '...', 'description' => '...']
     */
    public static function section(string $section): array
    {
        return static::where('section', $section)->pluck('value', 'field_key')->toArray();
    }

    /**
     * Set/update a field and bust its cache.
     */
    public static function set(string $section, string $key, ?string $value): void
    {
        static::updateOrCreate(
            ['section' => $section, 'field_key' => $key],
            ['value' => $value]
        );
        Cache::forget("site_content_{$section}_{$key}");
    }
}
