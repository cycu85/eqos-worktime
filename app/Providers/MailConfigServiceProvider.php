<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Support\ServiceProvider;

class MailConfigServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Apply SMTP settings from database when the application boots
        if ($this->app->runningInConsole() === false) {
            try {
                // Only apply settings if database is available and migrated
                if (\Schema::hasTable('settings')) {
                    Setting::applySmtpSettings();
                }
            } catch (\Exception $e) {
                // Fail silently if database is not available (e.g., during migration)
                \Log::warning('Could not apply SMTP settings: ' . $e->getMessage());
            }
        }
    }
}