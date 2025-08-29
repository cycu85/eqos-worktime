<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VehicleController extends Controller
{
    public function index()
    {
        $vehicles = Vehicle::withCount(['tasks' => function ($query) {
            $query->active();
        }])
        ->orderBy('name')
        ->paginate(15);
        
        return view('vehicles.index', compact('vehicles'));
    }

    public function create()
    {
        $this->authorize('create', Vehicle::class);
        
        return view('vehicles.create');
    }

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

    public function show(Vehicle $vehicle)
    {
        $this->authorize('view', $vehicle);
        
        $vehicle->load(['tasks' => function ($query) {
            $query->with('leader')->orderBy('start_datetime', 'desc');
        }]);
        
        return view('vehicles.show', compact('vehicle'));
    }

    public function edit(Vehicle $vehicle)
    {
        $this->authorize('update', $vehicle);
        
        return view('vehicles.edit', compact('vehicle'));
    }

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