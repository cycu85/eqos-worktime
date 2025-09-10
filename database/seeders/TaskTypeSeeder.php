<?php

namespace Database\Seeders;

use App\Models\TaskType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TaskTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $taskTypes = [
            [
                'name' => 'Konserwacja',
                'description' => 'Prace konserwacyjne i naprawcze sprzętu',
                'active' => true,
            ],
            [
                'name' => 'Transport',
                'description' => 'Zadania transportowe i logistyczne',
                'active' => true,
            ],
            [
                'name' => 'Montaż',
                'description' => 'Prace montażowe i instalacyjne',
                'active' => true,
            ],
            [
                'name' => 'Przegląd techniczny',
                'description' => 'Okresowe przeglądy i kontrole techniczne',
                'active' => true,
            ],
            [
                'name' => 'Szkolenie',
                'description' => 'Szkolenia pracowników i prace edukacyjne',
                'active' => true,
            ],
            [
                'name' => 'Dokumentacja',
                'description' => 'Prace dokumentacyjne i administracyjne',
                'active' => true,
            ],
        ];

        foreach ($taskTypes as $taskType) {
            TaskType::firstOrCreate(
                ['name' => $taskType['name']],
                $taskType
            );
        }
    }
}
