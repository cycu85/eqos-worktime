<?php

namespace App\Http\Controllers;

use App\Models\TaskType;
use App\Models\TaskTypePrice;
use Illuminate\Http\Request;

class PriceListController extends Controller
{
    public function index()
    {
        $prices = TaskTypePrice::with('taskType')
            ->orderBy('valid_from', 'desc')
            ->orderBy('task_type_id')
            ->get();

        $taskTypes = TaskType::orderBy('name')->get();

        return view('finanse.price-list.index', compact('prices', 'taskTypes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'task_type_id' => 'required|exists:task_types,id',
            'price' => 'required|numeric|min:0',
            'valid_from' => 'required|date',
        ]);

        TaskTypePrice::create([
            'task_type_id' => $request->task_type_id,
            'price' => $request->price,
            'valid_from' => $request->valid_from,
        ]);

        return redirect()->route('finanse.price-list.index')
            ->with('success', 'Cena została dodana pomyślnie.');
    }

    public function update(Request $request, TaskTypePrice $price)
    {
        $request->validate([
            'task_type_id' => 'required|exists:task_types,id',
            'price' => 'required|numeric|min:0',
            'valid_from' => 'required|date',
        ]);

        $price->update([
            'task_type_id' => $request->task_type_id,
            'price' => $request->price,
            'valid_from' => $request->valid_from,
        ]);

        return redirect()->route('finanse.price-list.index')
            ->with('success', 'Cena została zaktualizowana pomyślnie.');
    }

    public function destroy(TaskTypePrice $price)
    {
        $price->delete();

        return redirect()->route('finanse.price-list.index')
            ->with('success', 'Cena została usunięta pomyślnie.');
    }
}
