<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->date('start_date')->nullable()->after('description');
            $table->date('end_date')->nullable()->after('start_date');
        });

        // Zaktualizuj istniejące zadania
        DB::statement("
            UPDATE tasks 
            SET start_date = DATE(start_datetime),
                end_date = CASE 
                    WHEN end_datetime IS NOT NULL THEN DATE(end_datetime)
                    ELSE DATE(start_datetime)
                END
            WHERE start_date IS NULL
        ");

        // Teraz ustaw pola jako wymagane
        Schema::table('tasks', function (Blueprint $table) {
            $table->date('start_date')->nullable(false)->change();
            $table->date('end_date')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn(['start_date', 'end_date']);
        });
    }
};
