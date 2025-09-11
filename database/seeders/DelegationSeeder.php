<?php

namespace Database\Seeders;

use App\Models\Delegation;
use App\Services\NBPService;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class DelegationSeeder extends Seeder
{
    public function run(): void
    {
        $nbpService = new NBPService();
        
        $delegations = [
            [
                'first_name' => 'Jan',
                'last_name' => 'Kowalski',
                'order_date' => now()->subDays(30),
                'departure_date' => now()->subDays(28),
                'departure_time' => '08:00',
                'arrival_date' => now()->subDays(26),
                'arrival_time' => '18:00',
                'travel_purpose' => 'Konferencja techniczna dotycząca nowych technologii w branży',
                'project' => 'Projekt modernizacji',
                'destination_city' => 'Berlin',
                'country' => 'Niemcy',
                'vehicle_registration' => 'DW 12345',
                'nights_count' => 2,
                'accommodation_limit' => 150.00,
                'breakfasts' => 1,
                'lunches' => 2,
                'dinners' => 1,
                'total_expenses' => 85.50,
                'delegation_status' => 'completed',
            ],
            [
                'first_name' => 'Anna',
                'last_name' => 'Nowak',
                'order_date' => now()->subDays(20),
                'departure_date' => now()->subDays(18),
                'departure_time' => '07:30',
                'arrival_date' => now()->subDays(18),
                'arrival_time' => '20:00',
                'travel_purpose' => 'Spotkanie z klientem w sprawie realizacji zamówienia',
                'project' => null,
                'destination_city' => 'Kraków',
                'country' => 'Polska',
                'vehicle_registration' => 'DW 67890',
                'nights_count' => 0,
                'accommodation_limit' => 0,
                'breakfasts' => 0,
                'lunches' => 1,
                'dinners' => 0,
                'total_expenses' => 25.00,
                'delegation_status' => 'completed',
            ],
            [
                'first_name' => 'Piotr',
                'last_name' => 'Wiśniewski',
                'order_date' => now()->subDays(10),
                'departure_date' => now()->addDays(5),
                'departure_time' => '09:00',
                'arrival_date' => now()->addDays(8),
                'arrival_time' => '16:00',
                'travel_purpose' => 'Szkolenie z obsługi nowego oprogramowania',
                'project' => 'Digitalizacja procesów',
                'destination_city' => 'Paryż',
                'country' => 'Francja',
                'vehicle_registration' => null,
                'nights_count' => 3,
                'accommodation_limit' => 200.00,
                'breakfasts' => 0,
                'lunches' => 2,
                'dinners' => 2,
                'total_expenses' => 0,
                'delegation_status' => 'approved',
            ],
            [
                'first_name' => 'Maria',
                'last_name' => 'Kowalczyk',
                'order_date' => now()->subDays(5),
                'departure_date' => now()->addDays(15),
                'departure_time' => '10:00',
                'arrival_date' => now()->addDays(16),
                'arrival_time' => '15:00',
                'travel_purpose' => 'Audit zakładu produkcyjnego',
                'project' => 'Kontrola jakości',
                'destination_city' => 'Wrocław',
                'country' => 'Polska',
                'vehicle_registration' => 'DW 11111',
                'nights_count' => 1,
                'accommodation_limit' => 120.00,
                'breakfasts' => 1,
                'lunches' => 0,
                'dinners' => 1,
                'total_expenses' => 0,
                'delegation_status' => 'draft',
            ],
        ];

        foreach ($delegations as $delegationData) {
            // Pobierz stawkę delegacji dla kraju
            $delegationData['delegation_rate'] = $nbpService->getDelegationRates($delegationData['country']);
            $delegationData['diet_amount_pln'] = $delegationData['delegation_rate'];

            // Jeśli to delegacja zagraniczna, pobierz kurs NBP
            if ($delegationData['country'] !== 'Polska') {
                $delegationData['exchange_rate'] = 4.2500; // Przykładowy kurs EUR
                $delegationData['nbp_table_date'] = now()->subDays(1);
                $delegationData['nbp_table_number'] = '180/A/NBP/2025';
                $delegationData['diet_amount_currency'] = round($delegationData['diet_amount_pln'] / $delegationData['exchange_rate'], 2);
            }

            // Dodaj wymagane pola z wartościami domyślnymi
            $delegationData['total_diet_pln'] = 0;
            $delegationData['total_diet_currency'] = 0;
            $delegationData['amount_to_pay'] = 0;

            $delegation = Delegation::create($delegationData);
            
            // Oblicz automatyczne wartości
            $delegation->calculateDuration()
                      ->calculateTotalDiet()
                      ->calculateAmountToPay()
                      ->save();
        }
    }
}
