<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Team;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Build base query
        if ($user->isAdmin() || $user->isKierownik()) {
            // Admin and Kierownik see all tasks
            $query = Task::with(['vehicle', 'leader', 'team']);
        } elseif ($user->isLider()) {
            // Lider sees only tasks assigned to them as leader
            $query = Task::with(['vehicle', 'leader', 'team'])->forUser($user->id);
        } else {
            // Pracownik sees only tasks where they are part of the team
            $query = $user->teamTasks()->with(['vehicle', 'leader', 'team']);
        }
        
        // Apply search
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%')
                  ->orWhere('team', 'like', '%' . $search . '%')
                  ->orWhere('notes', 'like', '%' . $search . '%')
                  ->orWhereHas('vehicle', function ($vq) use ($search) {
                      $vq->where('name', 'like', '%' . $search . '%')
                        ->orWhere('registration', 'like', '%' . $search . '%');
                  })
                  ->orWhereHas('leader', function ($lq) use ($search) {
                      $lq->where('name', 'like', '%' . $search . '%');
                  })
                  ->orWhereHas('team', function ($tq) use ($search) {
                      $tq->where('name', 'like', '%' . $search . '%');
                  });
            });
        }
        
        // Apply status filter
        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }
        
        // Apply vehicle filter
        if ($request->filled('vehicle_id')) {
            $query->where('vehicle_id', $request->get('vehicle_id'));
        }
        
        // Apply date range filter
        if ($request->filled('date_from')) {
            $query->where('start_datetime', '>=', $request->get('date_from'));
        }
        
        if ($request->filled('date_to')) {
            $query->where('start_datetime', '<=', $request->get('date_to') . ' 23:59:59');
        }
        
        // Apply sorting
        $sortBy = $request->get('sort', 'start_datetime');
        $sortOrder = $request->get('order', 'desc');
        
        $allowedSorts = ['start_datetime', 'title', 'status', 'created_at'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderBy('start_datetime', 'desc');
        }
        
        $tasks = $query->paginate(15)->appends($request->query());
        
        // Get filter options
        $vehicles = Vehicle::active()->orderBy('name')->get();
        $statuses = [
            'planned' => 'Planowane',
            'in_progress' => 'W trakcie', 
            'completed' => 'Ukończone',
            'cancelled' => 'Anulowane'
        ];
        
        return view('tasks.index', compact('tasks', 'vehicles', 'statuses'));
    }

    public function create()
    {
        $this->authorize('create', Task::class);
        
        $vehicles = Vehicle::active()->orderBy('name')->get();
        $users = User::whereIn('role', ['lider', 'pracownik'])->orderBy('name')->get();
        
        // Get user's team members if user is a leader
        $leaderTeamMembers = [];
        $currentUser = Auth::user();
        if ($currentUser->isLider()) {
            $leaderTeam = Team::where('leader_id', $currentUser->id)->first();
            if ($leaderTeam && $leaderTeam->members) {
                $leaderTeamMembers = User::whereIn('id', $leaderTeam->members)->pluck('name')->toArray();
            }
        }
        
        return view('tasks.create', compact('vehicles', 'users', 'leaderTeamMembers'));
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
            'team_id' => 'nullable|exists:teams,id',
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
        
        $task->load(['vehicle', 'leader', 'team']);
        
        return view('tasks.show', compact('task'));
    }

    public function edit(Task $task)
    {
        $this->authorize('update', $task);
        
        $vehicles = Vehicle::active()->orderBy('name')->get();
        $users = User::whereIn('role', ['lider', 'pracownik'])->orderBy('name')->get();
        
        // Get user's team members if user is a leader
        $leaderTeamMembers = [];
        $currentUser = Auth::user();
        if ($currentUser->isLider()) {
            $leaderTeam = Team::where('leader_id', $currentUser->id)->first();
            if ($leaderTeam && $leaderTeam->members) {
                $leaderTeamMembers = User::whereIn('id', $leaderTeam->members)->pluck('name')->toArray();
            }
        }
        
        return view('tasks.edit', compact('task', 'vehicles', 'users', 'leaderTeamMembers'));
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