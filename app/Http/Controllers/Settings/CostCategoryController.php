<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\CostCategory;
use Illuminate\Http\Request;

class CostCategoryController extends Controller
{
    public function index()
    {
        $categories = CostCategory::withCount('costs')->orderBy('name')->get();
        return view('settings.cost-categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:cost_categories',
            'description' => 'nullable|string',
        ]);

        CostCategory::create([
            'name' => $request->name,
            'description' => $request->description,
            'active' => true,
        ]);

        return redirect()->route('settings.cost-categories.index')
            ->with('success', 'Kategoria kosztów została dodana pomyślnie.');
    }

    public function update(Request $request, CostCategory $category)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:cost_categories,name,' . $category->id,
            'description' => 'nullable|string',
            'active' => 'boolean',
        ]);

        $category->update([
            'name' => $request->name,
            'description' => $request->description,
            'active' => $request->has('active'),
        ]);

        return redirect()->route('settings.cost-categories.index')
            ->with('success', 'Kategoria kosztów została zaktualizowana pomyślnie.');
    }

    public function destroy(CostCategory $category)
    {
        if ($category->costs()->count() > 0) {
            return redirect()->route('settings.cost-categories.index')
                ->with('error', 'Nie można usunąć kategorii, która jest używana w kosztach.');
        }

        $category->delete();

        return redirect()->route('settings.cost-categories.index')
            ->with('success', 'Kategoria kosztów została usunięta pomyślnie.');
    }

    public function toggleActive(CostCategory $category)
    {
        $category->update(['active' => !$category->active]);

        return redirect()->route('settings.cost-categories.index')
            ->with('success', 'Status kategorii kosztów został zmieniony.');
    }
}
