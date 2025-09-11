<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Delegation extends Model
{
    protected $fillable = [
        'first_name', 
        'last_name', 
        'order_date', 
        'departure_date', 
        'departure_time',
        'arrival_date', 
        'arrival_time', 
        'delegation_duration', 
        'travel_purpose',
        'project', 
        'vehicle_registration', 
        'destination_city', 
        'country',
        'diet_amount_pln', 
        'diet_amount_currency', 
        'accommodation_limit',
        'nbp_table_date', 
        'nbp_table_number', 
        'exchange_rate',
        'nights_count', 
        'breakfasts', 
        'lunches', 
        'dinners',
        'total_diet_pln', 
        'total_diet_currency', 
        'total_expenses', 
        'amount_to_pay',
        'delegation_status', 
        'employee_approval_date', 
        'supervisor_approval_date',
        'employee_approval_status', 
        'supervisor_approval_status', 
        'delegation_rate'
    ];

    protected $casts = [
        'order_date' => 'date',
        'departure_date' => 'date', 
        'arrival_date' => 'date',
        'employee_approval_date' => 'datetime',
        'supervisor_approval_date' => 'datetime',
        'nbp_table_date' => 'date'
    ];

    public function calculateDuration()
    {
        if (!$this->departure_date || !$this->arrival_date) {
            $this->delegation_duration = null;
            return $this;
        }

        if ($this->departure_time && $this->arrival_time) {
            $start = Carbon::parse($this->departure_date->format('Y-m-d') . ' ' . $this->departure_time);
            $end = Carbon::parse($this->arrival_date->format('Y-m-d') . ' ' . $this->arrival_time);
            
            $totalHours = $start->diffInHours($end);
            $days = intval($totalHours / 24);
            $remainingHours = $totalHours % 24;
            
            if ($days > 0 && $remainingHours > 0) {
                $this->delegation_duration = $days . ' ' . ($days === 1 ? 'doba' : 'doby') . ' i ' . $remainingHours . ' ' . ($remainingHours === 1 ? 'godzina' : 'godzin');
            } elseif ($days > 0) {
                $this->delegation_duration = $days . ' ' . ($days === 1 ? 'doba' : 'doby');
            } else {
                $this->delegation_duration = $remainingHours . ' ' . ($remainingHours === 1 ? 'godzina' : 'godzin');
            }
        } else {
            $start = Carbon::parse($this->departure_date);
            $end = Carbon::parse($this->arrival_date);
            $days = $start->diffInDays($end);
            $this->delegation_duration = $days . ' ' . ($days === 1 ? 'doba' : 'doby');
        }
        return $this;
    }

    public function calculateTotalDiet()
    {
        if (!$this->departure_date || !$this->arrival_date) {
            $this->total_diet_pln = 0;
            $this->total_diet_currency = 0;
            return $this;
        }

        // Obliczenie całkowitej ilości godzin delegacji
        if ($this->departure_time && $this->arrival_time) {
            $start = Carbon::parse($this->departure_date->format('Y-m-d') . ' ' . $this->departure_time);
            $end = Carbon::parse($this->arrival_date->format('Y-m-d') . ' ' . $this->arrival_time);
            $totalHours = $start->diffInHours($end);
        } else {
            // Jeśli nie ma godzin, używamy standardowej metody (różnica dni * 24)
            $start = Carbon::parse($this->departure_date);
            $end = Carbon::parse($this->arrival_date);
            $totalHours = $start->diffInHours($end);
        }

        // Dzielenie z resztą przez 24
        $fullDays = intval($totalHours / 24);
        $remainingHours = $totalHours % 24;

        // Bazowa dieta za pełne doby
        $baseDiet = $fullDays * $this->delegation_rate;

        // Dodatek za pozostałe godziny
        $hourlyAddition = 0;
        if ($remainingHours <= 8) {
            $hourlyAddition = ($this->delegation_rate * 1) / 3; // 1/3 stawki
        } elseif ($remainingHours > 8 && $remainingHours < 12) {
            $hourlyAddition = ($this->delegation_rate * 1) / 2; // 1/2 stawki
        } elseif ($remainingHours >= 12) {
            $hourlyAddition = $this->delegation_rate; // całą stawkę
        }

        // Łączna dieta przed odliczeniami za posiłki
        $totalDietBeforeMeals = $baseDiet + $hourlyAddition;

        // Odliczenia za posiłki (śniadanie -15%, obiad -30%, kolacja -30%)
        $breakfastDeduction = $this->breakfasts * 0.15;
        $lunchDeduction = $this->lunches * 0.30;
        $dinnerDeduction = $this->dinners * 0.30;
        $totalMealsDeduction = $breakfastDeduction + $lunchDeduction + $dinnerDeduction;
        $finalDiet = $totalDietBeforeMeals - ($totalDietBeforeMeals * $totalMealsDeduction);

        // Ustalenie kwot w zależności od kraju
        if ($this->country === 'Polska') {
            $this->total_diet_pln = $finalDiet;
            $this->total_diet_currency = null;
        } else {
            // Dla delegacji zagranicznych - dieta w EUR, przeliczenie na PLN
            $this->total_diet_currency = $finalDiet;
            if ($this->exchange_rate) {
                $this->total_diet_pln = $finalDiet * $this->exchange_rate;
            } else {
                $this->total_diet_pln = $finalDiet;
            }
        }
        
        return $this;
    }

    public function calculateAmountToPay()
    {
        $this->amount_to_pay = $this->total_diet_pln + $this->accommodation_limit - $this->total_expenses;
        return $this;
    }

    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    private function getDays()
    {
        if (!$this->departure_date || !$this->arrival_date) {
            return 1; // Default to 1 day if dates are not set
        }
        return Carbon::parse($this->departure_date)->diffInDays(Carbon::parse($this->arrival_date)) + 1;
    }
}
