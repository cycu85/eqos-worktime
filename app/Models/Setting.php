<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    /**
     * Get setting value by key
     */
    public static function get(string $key, $default = null)
    {
        $cacheKey = "setting_{$key}";
        
        return Cache::remember($cacheKey, 60 * 60 * 24, function() use ($key, $default) {
            $setting = static::where('key', $key)->first();
            return $setting ? $setting->value : $default;
        });
    }

    /**
     * Set setting value by key
     */
    public static function set(string $key, $value): void
    {
        static::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
        
        // Clear cache
        Cache::forget("setting_{$key}");
    }

    /**
     * Get app name
     */
    public static function getAppName(): string
    {
        return static::get('app_name', 'EQOS WorkTime');
    }

    /**
     * Get logo path
     */
    public static function getLogoPath(): ?string
    {
        return static::get('logo_path');
    }
}
