<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('delegation_settings')
            ->where('key', 'delegation_rate_abroad')
            ->update([
                'value' => '12.00',
                'description' => 'Stawka diety dla delegacji zagranicznych (EUR)',
                'updated_at' => now()
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('delegation_settings')
            ->where('key', 'delegation_rate_abroad')
            ->update([
                'value' => '50.00',
                'description' => 'Stawka diety dla delegacji zagranicznych (PLN)',
                'updated_at' => now()
            ]);
    }
};
