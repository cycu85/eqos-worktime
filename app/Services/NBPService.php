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
}