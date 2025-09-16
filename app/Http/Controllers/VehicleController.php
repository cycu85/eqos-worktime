<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Kontroler do zarządzania pojazdami
 *
 * Obsługuje CRUD operacje dla pojazdów/sprzętu,
 * filtrowanie, sortowanie i walidację.
 */
class VehicleController extends Controller
{
    /**
     * Wyświetl listę pojazdów z filtrowaniem i sortowaniem
     *
     * @param Request $request Żądanie HTTP zawierające parametry filtrów
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = Vehicle::withCount(['tasks' => function ($query) {
            $query->active();
        }]);

        // Filter by search
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('registration', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $status = $request->get('status');
            if ($status === 'active') {
                $query->where('is_active', true);
            } elseif ($status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        // Sorting
        $sortField = $request->get('sort', 'name');
        $sortDirection = $request->get('direction', 'asc');

        $allowedSortFields = ['name', 'registration', 'is_active', 'created_at', 'tasks_count'];
        if (!in_array($sortField, $allowedSortFields)) {
            $sortField = 'name';
        }

        if ($sortField === 'tasks_count') {
            $query->orderBy('tasks_count', $sortDirection);
        } else {
            $query->orderBy($sortField, $sortDirection);
        }

        // Add secondary sort by name if not already sorting by name
        if ($sortField !== 'name') {
            $query->orderBy('name', 'asc');
        }

        $vehicles = $query->paginate(15)->withQueryString();
        
        return view('vehicles.index', compact('vehicles'));
    }

    /**
     * Wyświetl formularz tworzenia nowego pojazdu
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $this->authorize('create', Vehicle::class);
        
        return view('vehicles.create');
    }

    /**
     * Zapisz nowy pojazd w bazie danych
     *
     * @param Request $request Dane pojazdu z formularza
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $this->authorize('create', Vehicle::class);
        
        $validated = $request->validate([
            'registration' => 'required|string|max:255|unique:vehicles,registration',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        $vehicle = Vehicle::create($validated);

        return redirect()->route('vehicles.show', $vehicle)
            ->with('success', 'Pojazd został utworzony pomyślnie.');
    }

    /**
     * Wyświetl szczegóły pojazdu
     *
     * @param Vehicle $vehicle Pojazd
     * @return \Illuminate\View\View
     */
    public function show(Vehicle $vehicle)
    {
        $this->authorize('view', $vehicle);
        
        $vehicle->load(['tasks' => function ($query) {
            $query->with('leader')->orderBy('start_date', 'desc');
        }]);
        
        return view('vehicles.show', compact('vehicle'));
    }

    /**
     * Wyświetl formularz edycji pojazdu
     *
     * @param Vehicle $vehicle Pojazd
     * @return \Illuminate\View\View
     */
    public function edit(Vehicle $vehicle)
    {
        $this->authorize('update', $vehicle);
        
        return view('vehicles.edit', compact('vehicle'));
    }

    /**
     * Zaktualizuj pojazd w bazie danych
     *
     * @param Request $request Dane pojazdu z formularza
     * @param Vehicle $vehicle Pojazd do aktualizacji
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Vehicle $vehicle)
    {
        $this->authorize('update', $vehicle);
        
        $validated = $request->validate([
            'registration' => 'required|string|max:255|unique:vehicles,registration,' . $vehicle->id,
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        $vehicle->update($validated);

        return redirect()->route('vehicles.show', $vehicle)
            ->with('success', 'Pojazd został zaktualizowany pomyślnie.');
    }

    /**
     * Usuń pojazd z bazy danych
     *
     * @param Vehicle $vehicle Pojazd do usunięcia
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Vehicle $vehicle)
    {
        $this->authorize('delete', $vehicle);
        
        // Check if vehicle has any tasks
        if ($vehicle->tasks()->count() > 0) {
            return back()->with('error', 'Nie można usunąć pojazdu, który ma przypisane zadania.');
        }
        
        $name = $vehicle->name;
        $vehicle->delete();

        return redirect()->route('vehicles.index')
            ->with('success', "Pojazd '{$name}' został usunięty pomyślnie.");
    }
}
