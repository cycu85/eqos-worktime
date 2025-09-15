<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Dodaj domyślne ustawienia SMTP do tabeli settings
        $smtpSettings = [
            ['key' => 'mail_mailer', 'value' => 'smtp'],
            ['key' => 'mail_host', 'value' => 'smtp.gmail.com'],
            ['key' => 'mail_port', 'value' => '587'],
            ['key' => 'mail_username', 'value' => ''],
            ['key' => 'mail_password', 'value' => ''],
            ['key' => 'mail_encryption', 'value' => 'tls'],
            ['key' => 'mail_from_address', 'value' => 'noreply@example.com'],
            ['key' => 'mail_from_name', 'value' => 'EQOS WorkTime'],
        ];

        foreach ($smtpSettings as $setting) {
            DB::table('settings')->updateOrInsert(
                ['key' => $setting['key']],
                [
                    'value' => $setting['value'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Usuń ustawienia SMTP
        $smtpKeys = [
            'mail_mailer',
            'mail_host', 
            'mail_port',
            'mail_username',
            'mail_password',
            'mail_encryption',
            'mail_from_address',
            'mail_from_name',
        ];

        DB::table('settings')->whereIn('key', $smtpKeys)->delete();
    }
};