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

    /**
     * Get SMTP settings
     */
    public static function getSmtpSettings(): array
    {
        return [
            'mail_mailer' => static::get('mail_mailer', 'smtp'),
            'mail_host' => static::get('mail_host', 'smtp.gmail.com'),
            'mail_port' => static::get('mail_port', '587'),
            'mail_username' => static::get('mail_username', ''),
            'mail_password' => static::get('mail_password', ''),
            'mail_encryption' => static::get('mail_encryption', 'tls'),
            'mail_from_address' => static::get('mail_from_address', 'noreply@example.com'),
            'mail_from_name' => static::get('mail_from_name', 'EQOS WorkTime'),
        ];
    }

    /**
     * Set SMTP settings
     */
    public static function setSmtpSettings(array $settings): void
    {
        $allowedKeys = [
            'mail_mailer', 'mail_host', 'mail_port', 'mail_username',
            'mail_password', 'mail_encryption', 'mail_from_address', 'mail_from_name'
        ];

        foreach ($settings as $key => $value) {
            if (in_array($key, $allowedKeys)) {
                static::set($key, $value);
            }
        }
    }

    /**
     * Apply SMTP settings to Laravel mail config
     */
    public static function applySmtpSettings(): void
    {
        $settings = static::getSmtpSettings();
        
        config([
            'mail.default' => $settings['mail_mailer'],
            'mail.mailers.smtp.host' => $settings['mail_host'],
            'mail.mailers.smtp.port' => (int) $settings['mail_port'],
            'mail.mailers.smtp.username' => $settings['mail_username'],
            'mail.mailers.smtp.password' => $settings['mail_password'],
            'mail.mailers.smtp.encryption' => $settings['mail_encryption'],
            'mail.from.address' => $settings['mail_from_address'],
            'mail.from.name' => $settings['mail_from_name'],
        ]);
    }
}
