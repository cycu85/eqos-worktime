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
                'description' => 'Pojazd służbowy do transportu narzędzi i materiałów budowlanych',
                'is_active' => true,
            ],
            [
                'registration' => 'WR 67890',
                'name' => 'Volkswagen Crafter',
                'description' => 'Duży van do większych projektów, wyposażony w windę hydrauliczną',
                'is_active' => true,
            ],
            [
                'registration' => 'WR 11111',
                'name' => 'Audi A4',
                'description' => 'Samochód osobowy do wizyt u klientów i spotkań biznesowych',
                'is_active' => true,
            ],
            [
                'registration' => 'WR 22222',
                'name' => 'Mercedes Sprinter',
                'description' => 'Van chłodniczy do transportu specjalistycznych materiałów',
                'is_active' => true,
            ],
            [
                'registration' => 'WR 33333',
                'name' => 'Iveco Daily',
                'description' => 'Pojazd z platformą do prac na wysokości',
                'is_active' => true,
            ],
            [
                'registration' => 'WR 44444',
                'name' => 'BMW X5',
                'description' => 'Samochód kierownictwa do wizyt u VIP klientów',
                'is_active' => true,
            ],
            [
                'registration' => 'WR 55555',
                'name' => 'MAN TGL',
                'description' => 'Ciężarówka do transportu ciężkich materiałów',
                'is_active' => true,
            ],
            [
                'registration' => 'WR 66666',
                'name' => 'Renault Kangoo',
                'description' => 'Mały van do ekspresowych dostaw w mieście',
                'is_active' => true,
            ],
            [
                'registration' => 'WR 77777',
                'name' => 'Toyota Hilux',
                'description' => 'Pickup do prac terenowych i trudnych warunków',
                'is_active' => true,
            ],
            [
                'registration' => 'WR 99999',
                'name' => 'Fiat Ducato (stary)',
                'description' => 'Van wycofany z eksploatacji - do naprawy',
                'is_active' => false,
            ],
        ];

        foreach ($vehicles as $vehicle) {
            Vehicle::create($vehicle);
        }
    }
}
