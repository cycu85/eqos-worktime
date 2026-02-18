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
        Schema::table('leasings', function (Blueprint $table) {
            // Usuń stare kolumny
            $table->dropForeign(['vehicle_id']);
            $table->dropColumn(['lessor', 'contract_number', 'date_from', 'date_to', 'payment_date']);
            // Zmień vehicle_id na nullable
            $table->foreignId('vehicle_id')->nullable()->change();
            // Dodaj nowe kolumny
            $table->string('name', 255)->after('id');
            $table->date('cost_date')->after('amount');
        });
    }

    public function down(): void
    {
        Schema::table('leasings', function (Blueprint $table) {
            $table->dropColumn(['name', 'cost_date']);
            $table->foreignId('vehicle_id')->nullable(false)->change();
            $table->string('lessor', 255)->after('leasing_cost_type_id');
            $table->string('contract_number', 255)->after('lessor');
            $table->date('date_from')->after('contract_number');
            $table->date('date_to')->after('date_from');
            $table->date('payment_date')->after('amount');
        });
    }
};
