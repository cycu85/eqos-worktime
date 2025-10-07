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
            // Dodaj 'ksiegowy' do ENUM role
            $table->enum('role', ['admin', 'kierownik', 'lider', 'pracownik', 'ksiegowy'])
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
            // Usuń 'ksiegowy' z ENUM role
            $table->enum('role', ['admin', 'kierownik', 'lider', 'pracownik'])
                  ->default('pracownik')
                  ->change();
        });
    }
};
