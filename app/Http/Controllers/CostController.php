<?php

namespace App\Http\Controllers;

use App\Models\Cost;
use App\Models\CostCategory;
use Illuminate\Http\Request;

class CostController extends Controller
{
    public function index(Request $request)
    {
        $query = Cost::with('category')->orderBy('cost_date', 'desc');

        if ($request->filled('date_from')) {
            $query->where('cost_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('cost_date', '<=', $request->date_to);
        }

        if ($request->filled('cost_category_id')) {
            $query->where('cost_category_id', $request->cost_category_id);
        }

        $costs = $query->get();
        $totalAmount = $costs->sum('amount');
        $categories = CostCategory::active()->orderBy('name')->get();

        return view('finanse.costs.index', compact('costs', 'totalAmount', 'categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'cost_date' => 'required|date',
            'cost_category_id' => 'required|exists:cost_categories,id',
            'description' => 'nullable|string',
        ]);

        Cost::create($request->only(['name', 'amount', 'cost_date', 'cost_category_id', 'description']));

        return redirect()->route('finanse.costs.index')
            ->with('success', 'Koszt został dodany pomyślnie.');
    }

    public function update(Request $request, Cost $cost)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'cost_date' => 'required|date',
            'cost_category_id' => 'required|exists:cost_categories,id',
            'description' => 'nullable|string',
        ]);

        $cost->update($request->only(['name', 'amount', 'cost_date', 'cost_category_id', 'description']));

        return redirect()->route('finanse.costs.index')
            ->with('success', 'Koszt został zaktualizowany pomyślnie.');
    }

    public function destroy(Cost $cost)
    {
        $cost->delete();

        return redirect()->route('finanse.costs.index')
            ->with('success', 'Koszt został usunięty pomyślnie.');
    }
}
