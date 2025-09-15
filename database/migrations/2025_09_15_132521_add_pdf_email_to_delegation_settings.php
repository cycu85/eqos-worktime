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
        // Dodaj ustawienie dla adresu email do wysyłania PDF delegacji
        DB::table('delegation_settings')->insert([
            'key' => 'pdf_email_address',
            'value' => '',
            'type' => 'email',
            'description' => 'Adres email do automatycznego wysyłania zaakceptowanych delegacji PDF',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('delegation_settings')->where('key', 'pdf_email_address')->delete();
    }
};