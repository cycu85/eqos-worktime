<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DelegationSetting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'type',
        'description',
    ];

    /**
     * Get setting value by key
     */
    public static function get(string $key, $default = null)
    {
        $setting = static::where('key', $key)->first();
        
        if (!$setting) {
            return $default;
        }

        return match ($setting->type) {
            'decimal' => (float) $setting->value,
            'integer' => (int) $setting->value,
            'boolean' => (bool) $setting->value,
            default => $setting->value,
        };
    }

    /**
     * Set setting value by key
     */
    public static function set(string $key, $value, string $type = 'string', string $description = null): void
    {
        static::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'type' => $type,
                'description' => $description,
            ]
        );
    }

    /**
     * Get all settings as key-value array
     */
    public static function getAllSettings(): array
    {
        return static::query()
            ->get()
            ->mapWithKeys(function ($setting) {
                $value = match ($setting->type) {
                    'decimal' => (float) $setting->value,
                    'integer' => (int) $setting->value,
                    'boolean' => (bool) $setting->value,
                    default => $setting->value,
                };
                
                return [$setting->key => $value];
            })
            ->toArray();
    }
}
