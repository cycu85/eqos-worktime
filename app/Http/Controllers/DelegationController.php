<?php

namespace App\Http\Controllers;

use App\Models\Delegation;
use App\Models\DelegationSetting;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\NBPService;
use App\Mail\DelegationPdfMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use PDF;

class DelegationController extends Controller
{
    protected $nbpService;

    public function __construct(NBPService $nbpService)
    {
        $this->nbpService = $nbpService;
    }

    public function index(Request $request)
    {
        $query = Delegation::query();
        
        // Role-based filtering
        $user = auth()->user();
        if (!$user->isAdmin() && !$user->isKierownik()) {
            // Pracownik/Lider sees only their own delegations
            $nameParts = explode(' ', trim($user->name), 2);
            $firstName = $nameParts[0] ?? '';
            $lastName = $nameParts[1] ?? '';
            
            $query->where(function($q) use ($firstName, $lastName) {
                $q->where('first_name', $firstName)
                  ->where('last_name', $lastName);
            });
        }

        // Search filter
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('travel_purpose', 'like', "%{$search}%")
                  ->orWhere('destination_city', 'like', "%{$search}%")
                  ->orWhere('country', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('delegation_status', $request->get('status'));
        }

        // Country filter
        if ($request->filled('country')) {
            $query->where('country', $request->get('country'));
        }

        // Date range filter
        if ($request->filled('date_from')) {
            $query->where('departure_date', '>=', $request->get('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->where('departure_date', '<=', $request->get('date_to'));
        }

        // Sorting
        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        
        $allowedSorts = ['id', 'first_name', 'last_name', 'departure_date', 'arrival_date', 'delegation_status', 'amount_to_pay', 'created_at'];
        if (in_array($sortField, $allowedSorts)) {
            if ($sortField === 'first_name' || $sortField === 'last_name') {
                $query->orderBy('first_name', $sortDirection)->orderBy('last_name', $sortDirection);
            } else {
                $query->orderBy($sortField, $sortDirection);
            }
        }

        $delegations = $query->paginate(20)->appends($request->query());

        // Get unique countries for filter dropdown
        $countries = Delegation::distinct()->pluck('country')->filter()->sort()->values();

        return view('delegations.index', compact('delegations', 'countries'));
    }

    public function create()
    {
        $vehicles = Vehicle::active()->get();
        $users = User::active()->orderBy('name')->get();
        $countries = [
            'Polska', 'Niemcy', 'Francja', 'Włochy', 'Hiszpania', 
            'Czechy', 'Słowacja', 'Austria', 'Holandia', 'Belgia'
        ];
        
        // Get default values from settings
        $defaults = [
            'project' => \App\Models\DelegationSetting::get('default_project', ''),
            'travel_purpose' => \App\Models\DelegationSetting::get('default_travel_purpose', ''),
            'country' => \App\Models\DelegationSetting::get('default_country', 'Polska'),
            'destination_city' => \App\Models\DelegationSetting::get('default_city', ''),
        ];
        
        return view('delegations.create', compact('vehicles', 'users', 'countries', 'defaults'));
    }

    public function store(Request $request)
    {
        // Convert empty strings to null and format time fields
        $input = $request->all();
        
        // Parse employee_full_name to first_name and last_name
        if (isset($input['employee_full_name']) && !empty($input['employee_full_name'])) {
            $nameParts = explode(' ', trim($input['employee_full_name']), 2);
            $input['first_name'] = $nameParts[0] ?? '';
            $input['last_name'] = $nameParts[1] ?? '';
        }
        
        if (isset($input['departure_time'])) {
            if ($input['departure_time'] === '') {
                $input['departure_time'] = null;
            } else {
                // Convert HH:MM:SS to HH:MM
                $input['departure_time'] = substr($input['departure_time'], 0, 5);
            }
        }
        if (isset($input['arrival_time'])) {
            if ($input['arrival_time'] === '') {
                $input['arrival_time'] = null;
            } else {
                // Convert HH:MM:SS to HH:MM
                $input['arrival_time'] = substr($input['arrival_time'], 0, 5);
            }
        }
        // Convert vehicles array to comma-separated string
        if (isset($input['vehicles']) && is_array($input['vehicles'])) {
            $input['vehicle_registration'] = implode(',', array_filter($input['vehicles']));
            unset($input['vehicles']);
        } elseif (isset($input['vehicles']) && empty($input['vehicles'])) {
            $input['vehicle_registration'] = null;
            unset($input['vehicles']);
        }

        $request->merge($input);

        $validated = $request->validate([
            'employee_full_name' => 'required|string|max:255',
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'order_date' => 'required|date',
            'departure_date' => 'nullable|date|after_or_equal:order_date',
            'departure_time' => 'nullable|date_format:H:i',
            'arrival_date' => 'nullable|date|after_or_equal:departure_date',
            'arrival_time' => 'nullable|date_format:H:i',
            'travel_purpose' => 'required|string',
            'project' => 'nullable|string|max:255',
            'destination_city' => 'required|string|max:255',
            'country' => 'required|string|max:100',
            'vehicle_registration' => 'nullable|string|max:500',
            'accommodation_limit' => 'nullable|numeric|min:0',
            'nights_count' => 'integer|min:0',
            'breakfasts' => 'integer|min:0',
            'lunches' => 'integer|min:0',
            'dinners' => 'integer|min:0',
            'total_expenses' => 'nullable|numeric|min:0',
            'delegation_status' => 'nullable|in:draft,approved,completed,cancelled',
        ], [
            'departure_date.after_or_equal' => 'Data wyjazdu nie może być wcześniejsza niż data polecenia wyjazdu.',
            'arrival_date.after_or_equal' => 'Data przyjazdu nie może być wcześniejsza niż data wyjazdu.',
        ]);

        if ($validated['country'] !== 'Polska' && !empty($validated['arrival_date'])) {
            // Get exchange rate from the day before return date
            $arrivalDate = \Carbon\Carbon::parse($validated['arrival_date']);
            $rateDateString = $arrivalDate->subDay()->format('Y-m-d');
            
            $nbpData = $this->nbpService->getRateForDate('EUR', $rateDateString);
            if ($nbpData) {
                $validated['exchange_rate'] = $nbpData['rate'];
                $validated['nbp_table_number'] = $nbpData['no'];
                $validated['nbp_table_date'] = $nbpData['effectiveDate'];
            } else {
                // Fallback to current rate if historical rate not available
                $nbpData = $this->nbpService->getCurrentRates();
                if ($nbpData) {
                    $validated['exchange_rate'] = $nbpData['rate'];
                    $validated['nbp_table_number'] = $nbpData['no'];
                    $validated['nbp_table_date'] = $nbpData['effectiveDate'];
                }
            }
        }

        $validated['delegation_rate'] = $this->nbpService->getDelegationRates($validated['country']);
        
        if ($validated['country'] === 'Polska') {
            $validated['diet_amount_pln'] = $validated['delegation_rate'];
            $validated['diet_amount_currency'] = null;
        } else {
            // For foreign countries, rate is in EUR, need to convert to PLN
            $validated['diet_amount_currency'] = $validated['delegation_rate']; // EUR amount
            if (isset($validated['exchange_rate'])) {
                $validated['diet_amount_pln'] = $validated['delegation_rate'] * $validated['exchange_rate'];
            } else {
                $validated['diet_amount_pln'] = $validated['delegation_rate']; // fallback
            }
        }
        
        // Initialize calculated fields with default values
        $validated['total_diet_pln'] = 0;
        $validated['total_diet_currency'] = 0;
        $validated['amount_to_pay'] = 0;
        $validated['delegation_duration'] = null;

        $delegation = Delegation::create($validated);
        
        $delegation->calculateDuration()
                  ->calculateTotalDiet()
                  ->calculateAmountToPay()
                  ->save();

        return redirect()->route('delegations.show', $delegation)
                        ->with('success', 'Delegacja została utworzona.');
    }

    public function show(Delegation $delegation)
    {
        // Role-based access control
        $user = auth()->user();
        if (!$user->isAdmin() && !$user->isKierownik()) {
            // Check if user can view this delegation (only their own)
            $nameParts = explode(' ', trim($user->name), 2);
            $firstName = $nameParts[0] ?? '';
            $lastName = $nameParts[1] ?? '';
            
            if ($delegation->first_name !== $firstName || $delegation->last_name !== $lastName) {
                abort(403, 'Brak uprawnień do przeglądania tej delegacji.');
            }
        }
        
        return view('delegations.show', compact('delegation'));
    }

    public function edit(Delegation $delegation)
    {
        // Role-based access control
        $user = auth()->user();
        if (!$user->isAdmin() && !$user->isKierownik()) {
            // Check if user can edit this delegation (only their own)
            $nameParts = explode(' ', trim($user->name), 2);
            $firstName = $nameParts[0] ?? '';
            $lastName = $nameParts[1] ?? '';
            
            if ($delegation->first_name !== $firstName || $delegation->last_name !== $lastName) {
                abort(403, 'Brak uprawnień do edycji tej delegacji.');
            }
        }
        
        $vehicles = Vehicle::active()->get();
        $users = User::active()->orderBy('name')->get();
        $countries = [
            'Polska', 'Niemcy', 'Francja', 'Włochy', 'Hiszpania', 
            'Czechy', 'Słowacja', 'Austria', 'Holandia', 'Belgia'
        ];
        
        return view('delegations.edit', compact('delegation', 'vehicles', 'users', 'countries'));
    }

    public function update(Request $request, Delegation $delegation)
    {
        // Role-based access control
        $user = auth()->user();
        if (!$user->isAdmin() && !$user->isKierownik()) {
            // Check if user can update this delegation (only their own)
            $nameParts = explode(' ', trim($user->name), 2);
            $firstName = $nameParts[0] ?? '';
            $lastName = $nameParts[1] ?? '';
            
            if ($delegation->first_name !== $firstName || $delegation->last_name !== $lastName) {
                abort(403, 'Brak uprawnień do aktualizacji tej delegacji.');
            }
        }
        
        // Convert empty strings to null and format time fields
        $input = $request->all();
        
        // Parse employee_full_name to first_name and last_name
        if (isset($input['employee_full_name']) && !empty($input['employee_full_name'])) {
            $nameParts = explode(' ', trim($input['employee_full_name']), 2);
            $input['first_name'] = $nameParts[0] ?? '';
            $input['last_name'] = $nameParts[1] ?? '';
        }
        
        if (isset($input['departure_time'])) {
            if ($input['departure_time'] === '') {
                $input['departure_time'] = null;
            } else {
                // Convert HH:MM:SS to HH:MM
                $input['departure_time'] = substr($input['departure_time'], 0, 5);
            }
        }
        if (isset($input['arrival_time'])) {
            if ($input['arrival_time'] === '') {
                $input['arrival_time'] = null;
            } else {
                // Convert HH:MM:SS to HH:MM
                $input['arrival_time'] = substr($input['arrival_time'], 0, 5);
            }
        }

        // Convert vehicles array to comma-separated string
        if (isset($input['vehicles']) && is_array($input['vehicles'])) {
            $input['vehicle_registration'] = implode(',', array_filter($input['vehicles']));
            unset($input['vehicles']);
        } elseif (isset($input['vehicles']) && empty($input['vehicles'])) {
            $input['vehicle_registration'] = null;
            unset($input['vehicles']);
        }

        $request->merge($input);

        // Walidacja uprawnień do edycji daty polecenia wyjazdu
        $orderDateRules = 'required|date';
        if (!$user->isAdmin() && !$user->isKierownik()) {
            // Dla innych ról data polecenia wyjazdu musi pozostać bez zmian
            $orderDateRules .= '|in:' . $delegation->order_date->format('Y-m-d');
        }

        // Walidacja uprawnień do zmiany statusu delegacji
        $statusRules = 'nullable|in:draft,cancelled';
        if ($user->isAdmin() || $user->isKierownik()) {
            // Administratorzy i kierownicy mogą ustawić wszystkie statusy
            $statusRules = 'nullable|in:draft,approved,completed,cancelled';
        }

        $validated = $request->validate([
            'employee_full_name' => 'required|string|max:255',
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'order_date' => $orderDateRules,
            'departure_date' => 'nullable|date|after_or_equal:order_date|before_or_equal:today',
            'departure_time' => 'nullable|date_format:H:i',
            'arrival_date' => 'nullable|date|after_or_equal:departure_date|before_or_equal:today',
            'arrival_time' => 'nullable|date_format:H:i',
            'travel_purpose' => 'required|string',
            'project' => 'nullable|string|max:255',
            'destination_city' => 'required|string|max:255',
            'country' => 'required|string|max:100',
            'vehicle_registration' => 'nullable|string|max:500',
            'accommodation_limit' => 'nullable|numeric|min:0',
            'nights_count' => 'integer|min:0',
            'breakfasts' => 'integer|min:0',
            'lunches' => 'integer|min:0',
            'dinners' => 'integer|min:0',
            'total_expenses' => 'nullable|numeric|min:0',
            'delegation_status' => $statusRules,
        ], [
            'order_date.in' => 'Brak uprawnień do zmiany daty polecenia wyjazdu. Tylko administrator i kierownik mogą edytować to pole.',
            'departure_date.after_or_equal' => 'Data wyjazdu nie może być wcześniejsza niż data polecenia wyjazdu.',
            'departure_date.before_or_equal' => 'Data wyjazdu nie może być w przyszłości.',
            'arrival_date.after_or_equal' => 'Data przyjazdu nie może być wcześniejsza niż data wyjazdu.',
            'arrival_date.before_or_equal' => 'Data przyjazdu nie może być w przyszłości.',
        ]);

        if ($validated['country'] !== 'Polska' && !empty($validated['arrival_date'])) {
            // Get exchange rate from the day before return date
            $arrivalDate = \Carbon\Carbon::parse($validated['arrival_date']);
            $rateDateString = $arrivalDate->subDay()->format('Y-m-d');
            
            $nbpData = $this->nbpService->getRateForDate('EUR', $rateDateString);
            if ($nbpData) {
                $validated['exchange_rate'] = $nbpData['rate'];
                $validated['nbp_table_number'] = $nbpData['no'];
                $validated['nbp_table_date'] = $nbpData['effectiveDate'];
            } else {
                // Fallback to current rate if historical rate not available
                $nbpData = $this->nbpService->getCurrentRates();
                if ($nbpData) {
                    $validated['exchange_rate'] = $nbpData['rate'];
                    $validated['nbp_table_number'] = $nbpData['no'];
                    $validated['nbp_table_date'] = $nbpData['effectiveDate'];
                }
            }
        }

        $validated['delegation_rate'] = $this->nbpService->getDelegationRates($validated['country']);
        
        if ($validated['country'] === 'Polska') {
            $validated['diet_amount_pln'] = $validated['delegation_rate'];
            $validated['diet_amount_currency'] = null;
        } else {
            // For foreign countries, rate is in EUR, need to convert to PLN
            $validated['diet_amount_currency'] = $validated['delegation_rate']; // EUR amount
            if (isset($validated['exchange_rate'])) {
                $validated['diet_amount_pln'] = $validated['delegation_rate'] * $validated['exchange_rate'];
            } else {
                $validated['diet_amount_pln'] = $validated['delegation_rate']; // fallback
            }
        }
        
        // Initialize calculated fields with current values or defaults
        if (!isset($validated['total_diet_pln'])) {
            $validated['total_diet_pln'] = $delegation->total_diet_pln ?? 0;
        }
        if (!isset($validated['total_diet_currency'])) {
            $validated['total_diet_currency'] = $delegation->total_diet_currency ?? 0;
        }
        if (!isset($validated['amount_to_pay'])) {
            $validated['amount_to_pay'] = $delegation->amount_to_pay ?? 0;
        }

        $delegation->update($validated);
        
        $delegation->calculateDuration()
                  ->calculateTotalDiet()
                  ->calculateAmountToPay()
                  ->save();

        return redirect()->route('delegations.show', $delegation)
                        ->with('success', 'Delegacja została zaktualizowana.');
    }

    public function destroy(Delegation $delegation)
    {
        // Role-based access control
        $user = auth()->user();
        if (!$user->isAdmin() && !$user->isKierownik()) {
            // Check if user can delete this delegation (only their own)
            $nameParts = explode(' ', trim($user->name), 2);
            $firstName = $nameParts[0] ?? '';
            $lastName = $nameParts[1] ?? '';
            
            if ($delegation->first_name !== $firstName || $delegation->last_name !== $lastName) {
                abort(403, 'Brak uprawnień do usunięcia tej delegacji.');
            }
        }
        
        $delegation->delete();
        
        return redirect()->route('delegations.index')
                        ->with('success', 'Delegacja została usunięta.');
    }

    public function employeeApproval(Delegation $delegation)
    {
        // Check if user is the employee for this delegation
        $user = auth()->user();
        $nameParts = explode(' ', trim($user->name), 2);
        $firstName = $nameParts[0] ?? '';
        $lastName = $nameParts[1] ?? '';
        
        if ($delegation->first_name !== $firstName || $delegation->last_name !== $lastName) {
            abort(403, 'Możesz zaakceptować tylko swoją delegację.');
        }

        // Check if all required dates and times are filled
        if (!$delegation->departure_date || !$delegation->arrival_date || 
            !$delegation->departure_time || !$delegation->arrival_time) {
            return redirect()->back()->with('error', 'Wszystkie daty i godziny muszą być uzupełnione przed akceptacją.');
        }

        // Check if required fields for employee approval are filled
        if (empty($delegation->project)) {
            return redirect()->back()->with('error', 'Pole "Projekt" musi być uzupełnione przed akceptacją delegacji.');
        }

        if (empty($delegation->vehicle_registration)) {
            return redirect()->back()->with('error', 'Pole "Pojazdy" musi być uzupełnione przed akceptacją delegacji.');
        }

        // Check if not already approved
        if ($delegation->employee_approval_status === 'approved') {
            return redirect()->back()->with('error', 'Delegacja została już przez Ciebie zaakceptowana.');
        }

        $delegation->update([
            'employee_approval_status' => 'approved',
            'employee_approval_date' => now(),
            'delegation_status' => 'employee_approved'
        ]);

        return redirect()->back()->with('success', 'Delegacja została zaakceptowana przez pracownika.');
    }

    public function supervisorApproval(Delegation $delegation)
    {
        $user = auth()->user();
        
        // Check if user is kierownik or admin
        if (!$user->isKierownik() && !$user->isAdmin()) {
            abort(403, 'Brak uprawnień do akceptacji delegacji.');
        }

        // Check if employee has approved first
        if ($delegation->employee_approval_status !== 'approved') {
            return redirect()->back()->with('error', 'Delegacja musi być najpierw zaakceptowana przez pracownika.');
        }

        // Check if not already approved by supervisor
        if ($delegation->supervisor_approval_status === 'approved') {
            return redirect()->back()->with('error', 'Delegacja została już zaakceptowana przez kierownika.');
        }

        $delegation->update([
            'supervisor_approval_status' => 'approved',
            'supervisor_approval_date' => now(),
            'delegation_status' => 'approved'
        ]);

        // Automatyczne wysyłanie PDF na email (jeśli skonfigurowane)
        $this->sendDelegationPdfByEmail($delegation);

        return redirect()->back()->with('success', 'Delegacja została zaakceptowana przez kierownika.');
    }

    public function revokeApproval(Delegation $delegation)
    {
        $user = auth()->user();
        
        // Only admin can revoke approval
        if (!$user->isAdmin()) {
            abort(403, 'Tylko administrator może cofnąć akceptację.');
        }

        $delegation->update([
            'supervisor_approval_status' => 'pending',
            'supervisor_approval_date' => null,
            'delegation_status' => 'employee_approved'
        ]);

        return redirect()->back()->with('success', 'Akceptacja kierownika została cofnięta. Delegacja powróciła do stanu zaakceptowanej przez pracownika.');
    }

    public function createGroup()
    {
        // Only admin and kierownik can create group delegations
        $user = auth()->user();
        if (!$user->isAdmin() && !$user->isKierownik()) {
            abort(403, 'Brak uprawnień do tworzenia delegacji grupowych.');
        }

        $vehicles = Vehicle::active()->get();
        $users = User::active()->orderBy('name')->get();
        $teams = \App\Models\Team::all();
        $countries = [
            'Polska', 'Niemcy', 'Francja', 'Włochy', 'Hiszpania', 
            'Czechy', 'Słowacja', 'Austria', 'Holandia', 'Belgia'
        ];
        
        // Get default values from settings
        $defaults = [
            'project' => \App\Models\DelegationSetting::get('default_project', ''),
            'travel_purpose' => \App\Models\DelegationSetting::get('default_travel_purpose', ''),
            'country' => \App\Models\DelegationSetting::get('default_country', 'Polska'),
            'destination_city' => \App\Models\DelegationSetting::get('default_city', ''),
        ];
        
        return view('delegations.create-group', compact('vehicles', 'users', 'teams', 'countries', 'defaults'));
    }

    public function storeGroup(Request $request)
    {
        // Only admin and kierownik can store group delegations
        $user = auth()->user();
        if (!$user->isAdmin() && !$user->isKierownik()) {
            abort(403, 'Brak uprawnień do tworzenia delegacji grupowych.');
        }

        // Handle selected employees from JSON or array
        $selectedEmployees = $request->input('selected_employees', []);
        if (empty($selectedEmployees) && $request->has('selected_employees_json')) {
            try {
                $jsonData = json_decode($request->input('selected_employees_json'), true);
                if (is_array($jsonData)) {
                    $selectedEmployees = $jsonData;
                    // Merge the decoded data into the request for validation
                    $request->merge(['selected_employees' => $selectedEmployees]);
                }
            } catch (\Exception $e) {
                // Silently handle JSON decode errors
            }
        }

        // Convert vehicles array to comma-separated string
        $input = $request->all();
        if (isset($input['vehicles']) && is_array($input['vehicles'])) {
            $input['vehicle_registration'] = implode(',', array_filter($input['vehicles']));
            unset($input['vehicles']);
            $request->merge($input);
        } elseif (isset($input['vehicles']) && empty($input['vehicles'])) {
            $input['vehicle_registration'] = null;
            unset($input['vehicles']);
            $request->merge($input);
        }

        $validated = $request->validate([
            'selected_employees' => 'required|array|min:1',
            'selected_employees.*' => 'exists:users,id',
            'order_date' => 'required|date',
            'departure_date' => 'nullable|date|after_or_equal:order_date',
            'departure_time' => 'nullable|date_format:H:i',
            'arrival_date' => 'nullable|date|after_or_equal:departure_date',
            'arrival_time' => 'nullable|date_format:H:i',
            'travel_purpose' => 'required|string',
            'project' => 'nullable|string|max:255',
            'destination_city' => 'required|string|max:255',
            'country' => 'required|string|max:100',
            'vehicle_registration' => 'nullable|string|max:500',
            'accommodation_limit' => 'nullable|numeric|min:0',
            'nights_count' => 'integer|min:0',
            'breakfasts' => 'integer|min:0',
            'lunches' => 'integer|min:0',
            'dinners' => 'integer|min:0',
            'total_expenses' => 'nullable|numeric|min:0',
        ], [
            'departure_date.after_or_equal' => 'Data wyjazdu nie może być wcześniejsza niż data polecenia wyjazdu.',
            'arrival_date.after_or_equal' => 'Data przyjazdu nie może być wcześniejsza niż data wyjazdu.',
            'selected_employees.required' => 'Musisz wybrać co najmniej jednego pracownika.',
        ]);

        $createdDelegations = [];
        $selectedUsers = User::active()->whereIn('id', $validated['selected_employees'])->get();

        foreach ($selectedUsers as $selectedUser) {
            $delegationData = $validated;
            
            // Set employee data
            $nameParts = explode(' ', trim($selectedUser->name), 2);
            $delegationData['first_name'] = $nameParts[0] ?? '';
            $delegationData['last_name'] = $nameParts[1] ?? '';

            // Handle currency and exchange rates
            if ($validated['country'] !== 'Polska' && !empty($validated['arrival_date'])) {
                $arrivalDate = \Carbon\Carbon::parse($validated['arrival_date']);
                $rateDateString = $arrivalDate->subDay()->format('Y-m-d');
                
                $nbpData = $this->nbpService->getRateForDate('EUR', $rateDateString);
                if ($nbpData) {
                    $delegationData['exchange_rate'] = $nbpData['rate'];
                    $delegationData['nbp_table_number'] = $nbpData['no'];
                    $delegationData['nbp_table_date'] = $nbpData['effectiveDate'];
                } else {
                    $nbpData = $this->nbpService->getCurrentRates();
                    if ($nbpData) {
                        $delegationData['exchange_rate'] = $nbpData['rate'];
                        $delegationData['nbp_table_number'] = $nbpData['no'];
                        $delegationData['nbp_table_date'] = $nbpData['effectiveDate'];
                    }
                }
            }

            $delegationData['delegation_rate'] = $this->nbpService->getDelegationRates($validated['country']);
            
            if ($validated['country'] === 'Polska') {
                $delegationData['diet_amount_pln'] = $delegationData['delegation_rate'];
                $delegationData['diet_amount_currency'] = null;
            } else {
                $delegationData['diet_amount_currency'] = $delegationData['delegation_rate'];
                if (isset($delegationData['exchange_rate'])) {
                    $delegationData['diet_amount_pln'] = $delegationData['delegation_rate'] * $delegationData['exchange_rate'];
                } else {
                    $delegationData['diet_amount_pln'] = $delegationData['delegation_rate'];
                }
            }
            
            // Initialize calculated fields
            $delegationData['total_diet_pln'] = 0;
            $delegationData['total_diet_currency'] = 0;
            $delegationData['amount_to_pay'] = 0;
            $delegationData['delegation_duration'] = null;
            $delegationData['delegation_status'] = 'draft';

            // Remove selected_employees array from data
            unset($delegationData['selected_employees']);

            $delegation = Delegation::create($delegationData);
            
            $delegation->calculateDuration()
                      ->calculateTotalDiet()
                      ->calculateAmountToPay()
                      ->save();

            $createdDelegations[] = $delegation;
        }

        $count = count($createdDelegations);
        return redirect()->route('delegations.index')
                        ->with('success', "Utworzono {$count} delegacji grupowych.");
    }

    /**
     * Generate PDF document for delegation
     */
    public function generatePdf(Delegation $delegation)
    {
        // Check if delegation is approved
        if ($delegation->delegation_status !== 'approved') {
            return redirect()->back()->with('error', 'PDF można generować tylko dla zaakceptowanych delegacji.');
        }

        // Calculate all necessary fields
        $delegation->calculateDuration()
                  ->calculateTotalDiet()
                  ->calculateAmountToPay();

        $pdf = \PDF::loadView('delegations.pdf', compact('delegation'));
        
        // Set paper size and orientation
        $pdf->setPaper('A4', 'portrait');
        
        // Set filename
        $filename = 'delegacja_' . $delegation->id . '_' . 
                   str_replace(' ', '_', $delegation->employee_full_name) . '.pdf';
        
        return $pdf->download($filename);
    }

    /**
     * Send delegation PDF via email if email is configured
     */
    private function sendDelegationPdfByEmail(Delegation $delegation)
    {
        try {
            // Pobierz adres email z ustawień delegacji
            $emailAddress = DelegationSetting::get('pdf_email_address');
            
            // Jeśli email nie jest skonfigurowany, nie wysyłaj
            if (empty($emailAddress)) {
                return;
            }

            // Przelicz wszystkie pola potrzebne do PDF
            $delegation->calculateDuration()
                      ->calculateTotalDiet()
                      ->calculateAmountToPay();

            // Wygeneruj PDF jako string
            $pdf = \PDF::loadView('delegations.pdf', compact('delegation'));
            $pdf->setPaper('A4', 'portrait');
            $pdfContent = $pdf->output();

            // Wyślij email z PDF w załączniku
            Mail::to($emailAddress)->send(new DelegationPdfMail($delegation, $pdfContent));
            
        } catch (\Exception $e) {
            // Loguj błąd, ale nie przerywaj procesu akceptacji
            \Log::error('Błąd wysyłania PDF delegacji przez email: ' . $e->getMessage(), [
                'delegation_id' => $delegation->id,
                'email' => $emailAddress ?? 'brak'
            ]);
        }
    }
}
