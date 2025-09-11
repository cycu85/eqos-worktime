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
        Schema::create('delegation_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('string'); // string, decimal, integer
            $table->string('description')->nullable();
            $table->timestamps();
        });

        // Insert default settings
        DB::table('delegation_settings')->insert([
            [
                'key' => 'delegation_rate_poland',
                'value' => '45.00',
                'type' => 'decimal',
                'description' => 'Stawka diety dla delegacji krajowych (PLN)',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'delegation_rate_abroad',
                'value' => '12.00',
                'type' => 'decimal',
                'description' => 'Stawka diety dla delegacji zagranicznych (EUR)',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'default_project',
                'value' => '',
                'type' => 'string',
                'description' => 'Domyślny projekt',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'default_travel_purpose',
                'value' => '',
                'type' => 'string',
                'description' => 'Domyślny cel podróży',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'default_country',
                'value' => 'Polska',
                'type' => 'string',
                'description' => 'Domyślny kraj',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'default_city',
                'value' => '',
                'type' => 'string',
                'description' => 'Domyślna miejscowość',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delegation_settings');
    }
};
