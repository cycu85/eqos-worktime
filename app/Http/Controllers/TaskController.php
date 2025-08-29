<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        if ($user->isAdmin() || $user->isKierownik()) {
            $tasks = Task::with(['vehicle', 'leader'])
                ->orderBy('start_datetime', 'desc')
                ->paginate(15);
        } else {
            $tasks = Task::with(['vehicle', 'leader'])
                ->forUser($user->id)
                ->orderBy('start_datetime', 'desc')
                ->paginate(15);
        }
        
        return view('tasks.index', compact('tasks'));
    }

    public function create()
    {
        $this->authorize('create', Task::class);
        
        $vehicles = Vehicle::active()->orderBy('name')->get();
        
        return view('tasks.create', compact('vehicles'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Task::class);
        
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_datetime' => 'required|date',
            'end_datetime' => 'nullable|date|after:start_datetime',
            'vehicle_id' => 'required|exists:vehicles,id',
            'team' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'status' => 'in:planned,in_progress,completed,cancelled',
        ]);

        $validated['leader_id'] = Auth::id();

        $task = Task::create($validated);

        return redirect()->route('tasks.show', $task)
            ->with('success', 'Zadanie zostało utworzone pomyślnie.');
    }

    public function show(Task $task)
    {
        $this->authorize('view', $task);
        
        $task->load(['vehicle', 'leader']);
        
        return view('tasks.show', compact('task'));
    }

    public function edit(Task $task)
    {
        $this->authorize('update', $task);
        
        $vehicles = Vehicle::active()->orderBy('name')->get();
        
        return view('tasks.edit', compact('task', 'vehicles'));
    }

    public function update(Request $request, Task $task)
    {
        $this->authorize('update', $task);
        
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_datetime' => 'required|date',
            'end_datetime' => 'nullable|date|after:start_datetime',
            'vehicle_id' => 'required|exists:vehicles,id',
            'team' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'status' => 'in:planned,in_progress,completed,cancelled',
        ]);

        $task->update($validated);

        return redirect()->route('tasks.show', $task)
            ->with('success', 'Zadanie zostało zaktualizowane pomyślnie.');
    }

    public function destroy(Task $task)
    {
        $this->authorize('delete', $task);
        
        $task->delete();

        return redirect()->route('tasks.index')
            ->with('success', 'Zadanie zostało usunięte pomyślnie.');
    }
}