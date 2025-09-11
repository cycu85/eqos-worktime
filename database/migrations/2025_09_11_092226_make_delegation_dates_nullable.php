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
            $table->date('departure_date')->nullable()->change();
            $table->date('arrival_date')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('delegations', function (Blueprint $table) {
            $table->date('departure_date')->nullable(false)->change();
            $table->date('arrival_date')->nullable(false)->change();
        });
    }
};
