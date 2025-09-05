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
        Schema::table('tasks', function (Blueprint $table) {
            // Change the enum to include 'accepted' status
            $table->enum('status', ['planned', 'in_progress', 'completed', 'cancelled', 'accepted'])->default('planned')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            // Revert to original enum without 'accepted'
            $table->enum('status', ['planned', 'in_progress', 'completed', 'cancelled'])->default('planned')->change();
        });
    }
};
