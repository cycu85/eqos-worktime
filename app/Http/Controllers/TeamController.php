<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Kontroler do zarządzania zespołami
 *
 * Obsługuje CRUD operacje dla zespołów pracowniczych,
 * przypisywanie liderów, członków i pojazdów.
 */
class TeamController extends Controller
{
    /**
     * Wyświetl listę zespołów z filtrowaniem i sortowaniem
     *
     * @param Request $request Żądanie HTTP zawierające parametry filtrów
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Team::class);
        
        $query = Team::with(['creator', 'leader', 'vehicles']);
        
        // Apply search
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%');
            });
        }
        
        // Apply active filter
        if ($request->has('status')) {
            $status = $request->get('status');
            if ($status === 'active') {
                $query->where('active', true);
            } elseif ($status === 'inactive') {
                $query->where('active', false);
            }
            // If status is empty string (all teams), don't apply any filter
        }
        // By default show all teams when no status is specified
        
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

    /**
     * Wyświetl formularz tworzenia nowego zespołu
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $this->authorize('create', Team::class);
        
        // Get leaders who are not already assigned to other active teams
        $assignedLeaderIds = Team::active()->whereNotNull('leader_id')->pluck('leader_id');
        $leaders = User::active()
            ->where('role', 'lider')
            ->whereNotIn('id', $assignedLeaderIds)
            ->orderBy('name')
            ->get();
            
        // Get workers who are not members of other active teams
        $activeTeamMemberIds = Team::active()
            ->get()
            ->pluck('members')
            ->flatten()
            ->unique()
            ->toArray();
            
        $workers = User::active()
            ->where('role', 'pracownik')
            ->whereNotIn('id', $activeTeamMemberIds)
            ->orderBy('name')
            ->get();
        
        // Get all active vehicles (now teams can share vehicles)
        $vehicles = Vehicle::active()
            ->orderBy('name')
            ->get();
        
        return view('teams.create', compact('leaders', 'workers', 'vehicles'));
    }

    /**
     * Zapisz nowy zespół w bazie danych
     *
     * @param Request $request Dane zespołu z formularza
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $this->authorize('create', Team::class);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:teams,name',
            'description' => 'nullable|string',
            'leader_id' => 'required|exists:users,id',
            'vehicles' => 'nullable|array',
            'vehicles.*' => 'exists:vehicles,id',
            'members' => 'nullable|array',
            'members.*' => 'exists:users,id'
        ]);

        $validated['created_by'] = Auth::id();
        $vehicleIds = $validated['vehicles'] ?? [];
        unset($validated['vehicles']);

        $team = Team::create($validated);

        // Attach vehicles to the team
        if (!empty($vehicleIds)) {
            $team->vehicles()->attach($vehicleIds);
        }

        return redirect()->route('teams.index')->with('success', 'Zespół został utworzony pomyślnie.');
    }

    /**
     * Wyświetl szczegóły zespołu
     *
     * @param Team $team Zespół
     * @return \Illuminate\View\View
     */
    public function show(Team $team)
    {
        $this->authorize('view', $team);
        
        $team->load(['creator', 'leader', 'vehicles', 'tasks']);
        
        return view('teams.show', compact('team'));
    }

    /**
     * Wyświetl formularz edycji zespołu
     *
     * @param Team $team Zespół
     * @return \Illuminate\View\View
     */
    public function edit(Team $team)
    {
        $this->authorize('update', $team);
        
        // Get leaders who are not already assigned to other active teams (exclude current team)
        $assignedLeaderIds = Team::active()
            ->whereNotNull('leader_id')
            ->where('id', '!=', $team->id)
            ->pluck('leader_id');
        $leaders = User::active()
            ->where('role', 'lider')
            ->whereNotIn('id', $assignedLeaderIds)
            ->orderBy('name')
            ->get();
            
        // Get workers who are not members of other active teams (exclude current team)
        $activeTeamMemberIds = Team::active()
            ->where('id', '!=', $team->id)
            ->get()
            ->pluck('members')
            ->flatten()
            ->unique()
            ->toArray();
            
        $workers = User::active()
            ->where('role', 'pracownik')
            ->whereNotIn('id', $activeTeamMemberIds)
            ->orderBy('name')
            ->get();
        
        // Get all active vehicles (now teams can share vehicles)
        $vehicles = Vehicle::active()
            ->orderBy('name')
            ->get();

        // Get current members with their details for JavaScript
        $currentMembers = [];
        if ($team->members) {
            $currentMembers = User::active()->whereIn('id', $team->members)
                ->get()
                ->map(function($user) {
                    return [
                        'id' => $user->id,
                        'name' => $user->name,
                        'role' => $user->role
                    ];
                })->toArray();
        }
        
        return view('teams.edit', compact('team', 'leaders', 'workers', 'vehicles', 'currentMembers'));
    }

    /**
     * Zaktualizuj zespół w bazie danych
     *
     * @param Request $request Dane zespołu z formularza
     * @param Team $team Zespół do aktualizacji
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Team $team)
    {
        $this->authorize('update', $team);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:teams,name,' . $team->id,
            'description' => 'nullable|string',
            'leader_id' => 'required|exists:users,id',
            'vehicles' => 'nullable|array',
            'vehicles.*' => 'exists:vehicles,id',
            'members' => 'nullable|array',
            'members.*' => 'exists:users,id',
            'active' => 'boolean'
        ]);

        $vehicleIds = $validated['vehicles'] ?? [];
        unset($validated['vehicles']);

        $team->update($validated);

        // Sync vehicles with the team
        $team->vehicles()->sync($vehicleIds);

        return redirect()->route('teams.index')->with('success', 'Zespół został zaktualizowany pomyślnie.');
    }

    /**
     * Usuń zespół z bazy danych
     *
     * @param Team $team Zespół do usunięcia
     * @return \Illuminate\Http\RedirectResponse
     */
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
