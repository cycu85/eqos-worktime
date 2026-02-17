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
        Schema::create('leasings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained('vehicles')->onDelete('cascade');
            $table->foreignId('leasing_cost_type_id')->constrained('leasing_cost_types')->onDelete('restrict');
            $table->string('lessor', 255);
            $table->string('contract_number', 255);
            $table->date('date_from');
            $table->date('date_to');
            $table->decimal('amount', 10, 2);
            $table->date('payment_date');
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leasings');
    }
};
