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
        Schema::table('delegations', function (Blueprint $table) {
            // Increase vehicle_registration column length to support multiple vehicles
            $table->string('vehicle_registration', 500)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('delegations', function (Blueprint $table) {
            // Revert back to original length (assuming it was 255 or similar)
            $table->string('vehicle_registration', 255)->nullable()->change();
        });
    }
};
