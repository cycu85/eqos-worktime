<?php

namespace App\Http\Controllers;

use App\Models\Leasing;
use App\Models\LeasingCostType;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class LeasingController extends Controller
{
    public function index(Request $request)
    {
        $query = Leasing::with(['vehicle', 'leasingCostType'])->orderBy('cost_date', 'desc');

        if ($request->filled('date_from')) {
            $query->where('cost_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('cost_date', '<=', $request->date_to);
        }

        if ($request->filled('leasing_cost_type_id')) {
            $query->where('leasing_cost_type_id', $request->leasing_cost_type_id);
        }

        if ($request->filled('vehicle_id')) {
            $query->where('vehicle_id', $request->vehicle_id);
        }

        $leasings = $query->get();
        $totalAmount = $leasings->sum('amount');
        $costTypes = LeasingCostType::active()->orderBy('name')->get();
        $vehicles = Vehicle::active()->orderBy('name')->get();

        return view('finanse.costs.leasing', compact('leasings', 'totalAmount', 'costTypes', 'vehicles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'leasing_cost_type_id' => 'required|exists:leasing_cost_types,id',
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'amount' => 'required|numeric|min:0',
            'cost_date' => 'required|date',
            'description' => 'nullable|string',
        ]);

        Leasing::create($request->only(['name', 'leasing_cost_type_id', 'vehicle_id', 'amount', 'cost_date', 'description']));

        return redirect()->route('finanse.leasing.index')
            ->with('success', 'Koszt leasingowy został dodany pomyślnie.');
    }

    public function update(Request $request, Leasing $leasing)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'leasing_cost_type_id' => 'required|exists:leasing_cost_types,id',
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'amount' => 'required|numeric|min:0',
            'cost_date' => 'required|date',
            'description' => 'nullable|string',
        ]);

        $leasing->update($request->only(['name', 'leasing_cost_type_id', 'vehicle_id', 'amount', 'cost_date', 'description']));

        return redirect()->route('finanse.leasing.index')
            ->with('success', 'Koszt leasingowy został zaktualizowany pomyślnie.');
    }

    public function destroy(Leasing $leasing)
    {
        $leasing->delete();

        return redirect()->route('finanse.leasing.index')
            ->with('success', 'Rekord leasingu został usunięty pomyślnie.');
    }
}
