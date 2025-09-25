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
        // Migruj istniejące przypisania pojazdów do zespołów
        $teams = DB::table('teams')->whereNotNull('vehicle_id')->get();

        foreach ($teams as $team) {
            DB::table('team_vehicles')->insert([
                'team_id' => $team->id,
                'vehicle_id' => $team->vehicle_id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Przywróć dane do tabeli teams (jeśli potrzebne)
        $teamVehicles = DB::table('team_vehicles')->get();

        foreach ($teamVehicles as $teamVehicle) {
            // Uwaga: W rollbacku bierzemy tylko pierwszy pojazd dla zespołu
            // ponieważ teams.vehicle_id może przechowywać tylko jeden pojazd
            $existingAssignment = DB::table('teams')
                ->where('id', $teamVehicle->team_id)
                ->whereNull('vehicle_id')
                ->first();

            if ($existingAssignment) {
                DB::table('teams')
                    ->where('id', $teamVehicle->team_id)
                    ->update(['vehicle_id' => $teamVehicle->vehicle_id]);
            }
        }

        // Wyczyść tabelę team_vehicles
        DB::table('team_vehicles')->truncate();
    }
};
