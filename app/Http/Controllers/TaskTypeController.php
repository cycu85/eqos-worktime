<?php

namespace App\Http\Controllers;

use App\Models\TaskType;
use Illuminate\Http\Request;

class TaskTypeController extends Controller
{
    public function index()
    {
        $taskTypes = TaskType::orderBy('name')->get();
        return view('settings.task-types.index', compact('taskTypes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:task_types',
            'description' => 'nullable|string',
        ]);

        TaskType::create([
            'name' => $request->name,
            'description' => $request->description,
            'active' => true,
        ]);

        return redirect()->route('settings.task-types.index')
            ->with('success', 'Typ zadania został dodany pomyślnie.');
    }

    public function update(Request $request, TaskType $taskType)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:task_types,name,' . $taskType->id,
            'description' => 'nullable|string',
            'active' => 'boolean',
        ]);

        $taskType->update([
            'name' => $request->name,
            'description' => $request->description,
            'active' => $request->has('active'),
        ]);

        return redirect()->route('settings.task-types.index')
            ->with('success', 'Typ zadania został zaktualizowany pomyślnie.');
    }

    public function destroy(TaskType $taskType)
    {
        // Sprawdź czy typ zadania nie jest używany
        if ($taskType->tasks()->count() > 0) {
            return redirect()->route('settings.task-types.index')
                ->with('error', 'Nie można usunąć typu zadania, który jest używany w zadaniach.');
        }

        $taskType->delete();

        return redirect()->route('settings.task-types.index')
            ->with('success', 'Typ zadania został usunięty pomyślnie.');
    }

    public function toggleActive(TaskType $taskType)
    {
        $taskType->update(['active' => !$taskType->active]);

        return redirect()->route('settings.task-types.index')
            ->with('success', 'Status typu zadania został zmieniony.');
    }
}