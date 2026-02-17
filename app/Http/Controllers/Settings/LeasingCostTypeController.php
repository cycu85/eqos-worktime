<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\LeasingCostType;
use Illuminate\Http\Request;

class LeasingCostTypeController extends Controller
{
    public function index()
    {
        $types = LeasingCostType::withCount('leasings')->orderBy('name')->get();
        return view('settings.leasing-cost-types.index', compact('types'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:leasing_cost_types',
        ]);

        LeasingCostType::create([
            'name' => $request->name,
            'active' => true,
        ]);

        return redirect()->route('settings.leasing-cost-types.index')
            ->with('success', 'Typ kosztu leasingu został dodany pomyślnie.');
    }

    public function update(Request $request, LeasingCostType $type)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:leasing_cost_types,name,' . $type->id,
        ]);

        $type->update([
            'name' => $request->name,
        ]);

        return redirect()->route('settings.leasing-cost-types.index')
            ->with('success', 'Typ kosztu leasingu został zaktualizowany pomyślnie.');
    }

    public function destroy(LeasingCostType $type)
    {
        if ($type->leasings()->count() > 0) {
            return redirect()->route('settings.leasing-cost-types.index')
                ->with('error', 'Nie można usunąć typu, który jest używany w rekordach leasingu.');
        }

        $type->delete();

        return redirect()->route('settings.leasing-cost-types.index')
            ->with('success', 'Typ kosztu leasingu został usunięty pomyślnie.');
    }

    public function toggleActive(LeasingCostType $type)
    {
        $type->update(['active' => !$type->active]);

        return redirect()->route('settings.leasing-cost-types.index')
            ->with('success', 'Status typu kosztu leasingu został zmieniony.');
    }
}
