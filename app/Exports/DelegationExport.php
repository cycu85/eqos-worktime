<?php

namespace App\Exports;

use App\Models\Delegation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DelegationExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function collection()
    {
        $query = Delegation::query();

        // Role-based filtering
        $user = Auth::user();
        if (!$user->isAdmin() && !$user->isKierownik() && !$user->isKsiegowy()) {
            // Pracownik/Lider sees only their own delegations
            $nameParts = explode(' ', trim($user->name), 2);
            $firstName = $nameParts[0] ?? '';
            $lastName = $nameParts[1] ?? '';

            $query->where(function($q) use ($firstName, $lastName) {
                $q->where('first_name', $firstName)
                  ->where('last_name', $lastName);
            });
        }

        // Apply same filters as in DelegationController
        if ($this->request->filled('search')) {
            $search = $this->request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('travel_purpose', 'like', "%{$search}%")
                  ->orWhere('destination_city', 'like', "%{$search}%")
                  ->orWhere('country', 'like', "%{$search}%");
            });
        }

        if ($this->request->filled('status')) {
            $query->where('delegation_status', $this->request->get('status'));
        }

        if ($this->request->filled('country')) {
            $query->where('country', $this->request->get('country'));
        }

        if ($this->request->filled('date_from')) {
            $query->where('departure_date', '>=', $this->request->get('date_from'));
        }

        if ($this->request->filled('date_to')) {
            $query->where('departure_date', '<=', $this->request->get('date_to'));
        }

        // Sorting
        $sortField = $this->request->get('sort', 'created_at');
        $sortDirection = $this->request->get('direction', 'desc');

        $allowedSorts = ['id', 'first_name', 'last_name', 'departure_date', 'arrival_date', 'delegation_status', 'amount_to_pay', 'created_at'];
        if (in_array($sortField, $allowedSorts)) {
            if ($sortField === 'first_name' || $sortField === 'last_name') {
                $query->orderBy('first_name', $sortDirection)->orderBy('last_name', $sortDirection);
            } else {
                $query->orderBy($sortField, $sortDirection);
            }
        } else {
            $query->orderBy('created_at', 'desc');
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'Imię i nazwisko',
            'Data wyjazdu',
            'Godzina wyjazdu',
            'Data powrotu',
            'Godzina powrotu',
            'Pojazd',
            'Projekt',
            'Cel wyjazdu',
            'Miejsce',
            'Suma diet w PLN',
            'Suma diet w walucie',
            'Waluta',
        ];
    }

    public function map($delegation): array
    {
        // Pełne imię i nazwisko
        $fullName = trim($delegation->first_name . ' ' . $delegation->last_name);

        // Data wyjazdu
        $departureDate = '';
        if ($delegation->departure_date) {
            $departureDate = $delegation->departure_date->format('Y-m-d');
        }

        // Godzina wyjazdu
        $departureTime = '';
        if ($delegation->departure_time) {
            $departureTime = substr($delegation->departure_time, 0, 5);
        }

        // Data powrotu
        $arrivalDate = '';
        if ($delegation->arrival_date) {
            $arrivalDate = $delegation->arrival_date->format('Y-m-d');
        }

        // Godzina powrotu
        $arrivalTime = '';
        if ($delegation->arrival_time) {
            $arrivalTime = substr($delegation->arrival_time, 0, 5);
        }

        // Pojazd - parsowanie z JSON
        $vehicles = '';
        if ($delegation->vehicle_registration) {
            $vehicleData = json_decode($delegation->vehicle_registration, true);
            if (is_array($vehicleData)) {
                $vehicles = implode(', ', $vehicleData);
            } else {
                $vehicles = $delegation->vehicle_registration;
            }
        }

        // Projekt
        $project = $delegation->project_name ?? '';

        // Cel wyjazdu
        $travelPurpose = $delegation->travel_purpose ?? '';

        // Miejsce (miasto + kraj)
        $destination = trim($delegation->destination_city . ', ' . $delegation->country);

        // Suma diet w PLN
        $totalDietPln = number_format($delegation->total_diet_pln ?? 0, 2, ',', ' ');

        // Suma diet w walucie
        $totalDietCurrency = '';
        $currency = '';
        if ($delegation->country !== 'Polska' && $delegation->total_diet_currency) {
            $totalDietCurrency = number_format($delegation->total_diet_currency, 2, ',', ' ');
            $currency = 'EUR';
        }

        return [
            $fullName,
            $departureDate,
            $departureTime,
            $arrivalDate,
            $arrivalTime,
            $vehicles,
            $project,
            $travelPurpose,
            $destination,
            $totalDietPln,
            $totalDietCurrency,
            $currency,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
