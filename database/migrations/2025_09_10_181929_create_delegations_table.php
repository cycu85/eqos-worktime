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
        Schema::create('delegations', function (Blueprint $table) {
            $table->id();
            
            // Podstawowe dane
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            
            // Daty i czasy
            $table->date('order_date'); // Data polecenia wyjazdu
            $table->date('departure_date'); // Data wyjazdu - przekroczenia granicy
            $table->time('departure_time')->nullable(); // Godzina wyjazdu - Przekroczenia granicy
            $table->date('arrival_date'); // Data przyjazdu- przekroczenia granicy
            $table->time('arrival_time')->nullable(); // Godzina przyjazdu - Przekroczenia granicy
            $table->string('delegation_duration', 50)->nullable(); // Czas delegacji
            
            // Szczegóły delegacji
            $table->text('travel_purpose'); // Cel podróży
            $table->string('project')->nullable(); // Projekt
            $table->string('vehicle_registration', 20)->nullable(); // Środki lokomocji
            $table->string('destination_city'); // Do miejscowości
            $table->string('country', 100); // Kraj
            
            // Kwoty i waluty
            $table->decimal('diet_amount_pln', 10, 2); // Kwota diety PLN
            $table->decimal('diet_amount_currency', 10, 2)->nullable(); // Kwota diety Waluta
            $table->decimal('accommodation_limit', 10, 2)->nullable(); // Kwota limitu za nocleg
            
            // NBP
            $table->date('nbp_table_date')->nullable(); // Tabela kursów NBP z dnia
            $table->string('nbp_table_number', 20)->nullable(); // Tabela kursów NBP numer
            $table->decimal('exchange_rate', 6, 4)->nullable(); // Kurs
            
            // Posiłki i noclegi
            $table->integer('nights_count')->default(0); // Ilość noclegów
            $table->integer('breakfasts')->default(0); // Śniadania
            $table->integer('lunches')->default(0); // Obiady
            $table->integer('dinners')->default(0); // Kolacje
            
            // Sumy
            $table->decimal('total_diet_pln', 10, 2); // Suma diet należnych PLN
            $table->decimal('total_diet_currency', 10, 2)->nullable(); // Suma diet należnych Waluta
            $table->decimal('total_expenses', 10, 2)->default(0.00); // Suma kwot poniesionych
            $table->decimal('amount_to_pay', 10, 2); // Do wypłaty dla pracownika
            
            // Statusy
            $table->enum('delegation_status', ['draft', 'approved', 'completed', 'cancelled'])->default('draft'); // Status delegacji
            $table->date('employee_approval_date')->nullable(); // Data akceptacji przez pracownika
            $table->date('supervisor_approval_date')->nullable(); // Data akceptacji przez przełożonego
            $table->enum('employee_approval_status', ['pending', 'approved', 'rejected'])->default('pending'); // Status akceptacji przez pracownika
            $table->enum('supervisor_approval_status', ['pending', 'approved', 'rejected'])->default('pending'); // Status akceptacji przez przełożonego
            $table->decimal('delegation_rate', 8, 2); // Stawka Delegacji
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delegations');
    }
};
