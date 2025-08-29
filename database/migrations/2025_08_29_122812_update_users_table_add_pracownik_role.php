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
        Schema::table('users', function (Blueprint $table) {
            // Change the ENUM to include 'pracownik' role
            $table->enum('role', ['admin', 'kierownik', 'lider', 'pracownik'])
                  ->default('pracownik')
                  ->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Revert back to original ENUM values
            $table->enum('role', ['admin', 'kierownik', 'lider'])
                  ->default('lider')
                  ->change();
        });
    }
};
