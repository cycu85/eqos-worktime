<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class NBPService
{
    public function getCurrentRates($currency = 'EUR')
    {
        $cacheKey = "nbp_rate_{$currency}_" . now()->toDateString();
        
        return Cache::remember($cacheKey, 3600, function() use ($currency) {
            $response = Http::get("https://api.nbp.pl/api/exchangerates/rates/a/{$currency}/");
            
            if ($response->successful()) {
                $data = $response->json();
                return [
                    'rate' => $data['rates'][0]['mid'],
                    'table' => $data['table'],
                    'no' => $data['rates'][0]['no'],
                    'effectiveDate' => $data['rates'][0]['effectiveDate']
                ];
            }
            
            return null;
        });
    }

    public function getDelegationRates($country = 'Polska')
    {
        // Get rates from settings
        $polandRate = \App\Models\DelegationSetting::get('delegation_rate_poland', 45.00);
        
        if ($country === 'Polska') {
            return $polandRate;
        } else {
            // For foreign countries, return rate in EUR (will be converted to PLN in controller)
            return \App\Models\DelegationSetting::get('delegation_rate_abroad', 12.00);
        }
    }

    public function getRateForDate($currency, $date)
    {
        $cacheKey = "nbp_rate_{$currency}_" . $date;

        return Cache::remember($cacheKey, 86400, function() use ($currency, $date) {
            $response = Http::get("https://api.nbp.pl/api/exchangerates/rates/a/{$currency}/{$date}/");

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'rate' => $data['rates'][0]['mid'],
                    'table' => $data['table'],
                    'no' => $data['rates'][0]['no'],
                    'effectiveDate' => $data['rates'][0]['effectiveDate']
                ];
            }

            return null;
        });
    }

    /**
     * Pobiera kurs z dnia poprzedzającego podaną datę.
     * Jeśli w tym dniu nie ma kursu (weekend/święto), cofa się dalej w czasie.
     *
     * @param string $currency Kod waluty (np. EUR)
     * @param string $arrivalDate Data przyjazdu (format Y-m-d)
     * @param int $maxDaysBack Maksymalna liczba dni do sprawdzenia wstecz (domyślnie 10)
     * @return array|null Dane kursu lub null jeśli nie znaleziono
     */
    public function getRateBeforeDate($currency, $arrivalDate, $maxDaysBack = 10)
    {
        $date = Carbon::parse($arrivalDate);

        // Rozpoczynamy od dnia poprzedzającego datę przyjazdu
        for ($i = 1; $i <= $maxDaysBack; $i++) {
            $checkDate = $date->copy()->subDays($i);
            $dateString = $checkDate->format('Y-m-d');

            $nbpData = $this->getRateForDate($currency, $dateString);

            if ($nbpData) {
                // Znaleziono kurs - zwracamy
                return $nbpData;
            }

            // Brak kursu - próbujemy kolejny dzień wstecz
        }

        // Nie znaleziono kursu w żadnym z poprzednich dni
        return null;
    }
}