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
        $query = Leasing::with(['vehicle', 'leasingCostType'])->orderBy('payment_date', 'desc');

        if ($request->filled('date_from')) {
            $query->where('payment_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('payment_date', '<=', $request->date_to);
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
            'vehicle_id' => 'required|exists:vehicles,id',
            'leasing_cost_type_id' => 'required|exists:leasing_cost_types,id',
            'lessor' => 'required|string|max:255',
            'contract_number' => 'required|string|max:255',
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
            'amount' => 'required|numeric|min:0',
            'payment_date' => 'required|date',
            'description' => 'nullable|string',
        ]);

        Leasing::create($request->only([
            'vehicle_id', 'leasing_cost_type_id', 'lessor', 'contract_number',
            'date_from', 'date_to', 'amount', 'payment_date', 'description',
        ]));

        return redirect()->route('finanse.leasing.index')
            ->with('success', 'Rekord leasingu został dodany pomyślnie.');
    }

    public function update(Request $request, Leasing $leasing)
    {
        $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'leasing_cost_type_id' => 'required|exists:leasing_cost_types,id',
            'lessor' => 'required|string|max:255',
            'contract_number' => 'required|string|max:255',
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
            'amount' => 'required|numeric|min:0',
            'payment_date' => 'required|date',
            'description' => 'nullable|string',
        ]);

        $leasing->update($request->only([
            'vehicle_id', 'leasing_cost_type_id', 'lessor', 'contract_number',
            'date_from', 'date_to', 'amount', 'payment_date', 'description',
        ]));

        return redirect()->route('finanse.leasing.index')
            ->with('success', 'Rekord leasingu został zaktualizowany pomyślnie.');
    }

    public function destroy(Leasing $leasing)
    {
        $leasing->delete();

        return redirect()->route('finanse.leasing.index')
            ->with('success', 'Rekord leasingu został usunięty pomyślnie.');
    }
}
