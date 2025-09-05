<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TeamController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Team::class);
        
        $query = Team::with(['creator', 'leader', 'vehicle']);
        
        // Apply search
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%');
            });
        }
        
        // Apply active filter
        if ($request->filled('status')) {
            $status = $request->get('status');
            if ($status === 'active') {
                $query->where('active', true);
            } elseif ($status === 'inactive') {
                $query->where('active', false);
            }
        } else {
            // By default show only active teams
            $query->active();
        }
        
        // Apply sorting
        $sortBy = $request->get('sort', 'name');
        $sortOrder = $request->get('direction', 'asc');
        
        $allowedSorts = ['name', 'created_at'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderBy('name', 'asc');
        }
        
        $teams = $query->paginate(15)->appends($request->query());
        
        return view('teams.index', compact('teams'));
    }

    public function create()
    {
        $this->authorize('create', Team::class);
        
        $leaders = User::where('role', 'lider')->orderBy('name')->get();
        $workers = User::where('role', 'pracownik')->orderBy('name')->get();
        $vehicles = Vehicle::active()->orderBy('name')->get();
        
        return view('teams.create', compact('leaders', 'workers', 'vehicles'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Team::class);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:teams,name',
            'description' => 'nullable|string',
            'leader_id' => 'required|exists:users,id',
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'members' => 'nullable|array',
            'members.*' => 'exists:users,id'
        ]);

        $validated['created_by'] = Auth::id();

        $team = Team::create($validated);

        return redirect()->route('teams.index')->with('success', 'Zespół został utworzony pomyślnie.');
    }

    public function show(Team $team)
    {
        $this->authorize('view', $team);
        
        $team->load(['creator', 'leader', 'vehicle', 'tasks']);
        
        return view('teams.show', compact('team'));
    }

    public function edit(Team $team)
    {
        $this->authorize('update', $team);
        
        $leaders = User::where('role', 'lider')->orderBy('name')->get();
        $workers = User::where('role', 'pracownik')->orderBy('name')->get();
        $vehicles = Vehicle::active()->orderBy('name')->get();
        
        return view('teams.edit', compact('team', 'leaders', 'workers', 'vehicles'));
    }

    public function update(Request $request, Team $team)
    {
        $this->authorize('update', $team);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:teams,name,' . $team->id,
            'description' => 'nullable|string',
            'leader_id' => 'required|exists:users,id',
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'members' => 'nullable|array',
            'members.*' => 'exists:users,id',
            'active' => 'boolean'
        ]);

        $team->update($validated);

        return redirect()->route('teams.index')->with('success', 'Zespół został zaktualizowany pomyślnie.');
    }

    public function destroy(Team $team)
    {
        $this->authorize('delete', $team);
        
        // Check if team has assigned tasks
        if ($team->tasks()->count() > 0) {
            return redirect()->route('teams.index')->with('error', 'Nie można usunąć zespołu, który ma przypisane zadania.');
        }

        $team->delete();

        return redirect()->route('teams.index')->with('success', 'Zespół został usunięty pomyślnie.');
    }
}
