<?php

namespace Database\Seeders;

use App\Models\Vehicle;
use Illuminate\Database\Seeder;

class VehicleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $vehicles = [
            [
                'registration' => 'WR 12345',
                'name' => 'Ford Transit Van',
                'description' => 'Pojazd służbowy do transportu narzędzi',
                'is_active' => true,
            ],
            [
                'registration' => 'WR 67890',
                'name' => 'Volkswagen Crafter',
                'description' => 'Duży van do większych projektów',
                'is_active' => true,
            ],
            [
                'registration' => 'WR 11111',
                'name' => 'Audi A4',
                'description' => 'Samochód osobowy do wizyt u klientów',
                'is_active' => true,
            ],
            [
                'registration' => 'WR 99999',
                'name' => 'Mercedes Sprinter',
                'description' => 'Van do długich tras',
                'is_active' => false,
            ],
        ];

        foreach ($vehicles as $vehicle) {
            Vehicle::create($vehicle);
        }
    }
}
