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
        Schema::create('task_type_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_type_id')->constrained('task_types')->onDelete('cascade');
            $table->decimal('price', 10, 2);
            $table->date('valid_from');
            $table->timestamps();

            // Indeks na task_type_id i valid_from dla szybszego wyszukiwania
            $table->index(['task_type_id', 'valid_from']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_type_prices');
    }
};
