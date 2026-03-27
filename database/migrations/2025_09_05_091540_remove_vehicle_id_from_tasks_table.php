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
        // Usuń indeks złożony zawierający vehicle_id (kompatybilność z SQLite)
        if (Schema::hasColumn('tasks', 'vehicle_id')) {
            try {
                Schema::table('tasks', function (Blueprint $table) {
                    $table->dropIndex('tasks_vehicle_id_start_datetime_index');
                });
            } catch (\Exception $e) {
                // Indeks może nie istnieć na tej instancji
            }
        }
        Schema::table('tasks', function (Blueprint $table) {
            if (Schema::hasColumn('tasks', 'vehicle_id')) {
                $table->dropForeign(['vehicle_id']);
                $table->dropColumn('vehicle_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->foreignId('vehicle_id')->nullable()->constrained('vehicles')->onDelete('set null');
        });
    }
};
