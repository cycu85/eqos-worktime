<?php

namespace App\Http\Controllers;

use App\Exports\TaskExport;
use App\Models\Task;
use App\Models\Team;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Build base query
        if ($user->isAdmin() || $user->isKierownik()) {
            // Admin and Kierownik see all tasks
            $query = Task::with(['vehicles', 'leader', 'team']);
        } elseif ($user->isLider()) {
            // Lider sees only tasks assigned to them as leader
            $query = Task::with(['vehicles', 'leader', 'team'])->forUser($user->id);
        } else {
            // Pracownik sees only tasks where they are part of the team
            $query = $user->teamTasks()->with(['vehicles', 'leader', 'team']);
        }
        
        // Apply search
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%')
                  ->orWhere('team', 'like', '%' . $search . '%')
                  ->orWhere('notes', 'like', '%' . $search . '%')
                  ->orWhereHas('vehicles', function ($vq) use ($search) {
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
            $query->whereHas('vehicles', function ($q) use ($request) {
                $q->where('vehicles.id', $request->get('vehicle_id'));
            });
        }
        
        // Apply date range filter
        if ($request->filled('date_from')) {
            $query->where('start_datetime', '>=', $request->get('date_from'));
        }
        
        if ($request->filled('date_to')) {
            $query->where('start_datetime', '<=', $request->get('date_to') . ' 23:59:59');
        }
        
        // Apply user filter
        if ($request->filled('user_id')) {
            $userId = $request->get('user_id');
            $query->where(function ($q) use ($userId) {
                $q->where('leader_id', $userId)
                  ->orWhere('team', 'like', '%' . User::find($userId)->name . '%');
            });
        }
        
        // Apply sorting
        $sortBy = $request->get('sort', 'title');
        $sortOrder = $request->get('direction', 'asc');
        
        $allowedSorts = ['title', 'start_datetime', 'status', 'created_at'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderBy('title', 'asc');
        }
        
        // Get all tasks for calendar (without pagination)
        $allTasks = clone $query;
        $allTasks = $allTasks->get();
        
        // Get paginated tasks for table
        $tasks = $query->paginate(15)->appends($request->query());
        
        // Get filter options
        $vehicles = Vehicle::active()->orderBy('name')->get();
        $users = User::orderBy('name')->get();
        $statuses = [
            'planned' => 'Planowane',
            'in_progress' => 'W trakcie', 
            'completed' => 'Ukończone',
            'cancelled' => 'Anulowane',
            'accepted' => 'Zaakceptowane'
        ];
        
        // Prepare tasks data for JavaScript calendar
        $calendarTasks = $allTasks->map(function($task) {
            return [
                'id' => $task->id,
                'title' => $task->title,
                'start_datetime' => $task->start_datetime->format('Y-m-d H:i:s'),
                'status' => $task->status,
                'leader' => $task->leader->name,
                'vehicles' => $task->vehicles->pluck('name')->join(', '),
                'url' => route('tasks.show', $task)
            ];
        });
        
        return view('tasks.index', compact('tasks', 'allTasks', 'calendarTasks', 'vehicles', 'users', 'statuses'));
    }

    public function create()
    {
        $this->authorize('create', Task::class);
        
        $vehicles = Vehicle::active()->orderBy('name')->get();
        $users = User::whereIn('role', ['lider', 'pracownik'])->orderBy('name')->get();
        $teams = Team::with('vehicle')->active()->orderBy('name')->get();
        
        // Get user's team members if user is a leader
        $leaderTeamMembers = [];
        $leaderTeam = null;
        $currentUser = Auth::user();
        if ($currentUser->isLider()) {
            $leaderTeam = Team::with('vehicle')->where('leader_id', $currentUser->id)->first();
            if ($leaderTeam && $leaderTeam->members) {
                $leaderTeamMembers = User::whereIn('id', $leaderTeam->members)->pluck('name')->toArray();
            }
        }
        
        return view('tasks.create', compact('vehicles', 'users', 'teams', 'leaderTeamMembers', 'leaderTeam'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Task::class);
        
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_datetime' => 'required|date',
            'end_datetime' => 'nullable|date|after:start_datetime',
            'vehicles' => 'required|array|min:1',
            'vehicles.*' => 'exists:vehicles,id',
            'team_id' => 'nullable|exists:teams,id',
            'team' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'status' => 'in:planned,in_progress,completed,cancelled',
        ]);

        // Ustaw lidera na podstawie wybranego teamu lub aktualnego użytkownika
        if ($validated['team_id']) {
            // Jeśli wybrano team, ustaw lidera tego teamu jako lidera zadania
            $team = \App\Models\Team::find($validated['team_id']);
            $validated['leader_id'] = $team ? $team->leader_id : Auth::id();
        } else {
            // Jeśli nie wybrano teamu, aktualny użytkownik staje się liderem
            $validated['leader_id'] = Auth::id();
        }
        
        $vehicleIds = $validated['vehicles'];
        unset($validated['vehicles']);

        $task = Task::create($validated);
        $task->vehicles()->attach($vehicleIds);

        return redirect()->route('tasks.show', $task)
            ->with('success', 'Zadanie zostało utworzone pomyślnie.');
    }

    public function show(Task $task)
    {
        $this->authorize('view', $task);
        
        $task->load(['vehicles', 'leader', 'team']);
        
        return view('tasks.show', compact('task'));
    }

    public function edit(Task $task)
    {
        $this->authorize('update', $task);
        
        // Check if task is locked for current user
        if ($task->isLockedForUser(auth()->user())) {
            return redirect()->route('tasks.show', $task)
                ->with('error', 'To zadanie jest zablokowane do edycji. Status "Zaakceptowane" może być zmieniany tylko przez Administratora lub Kierownika.');
        }
        
        $vehicles = Vehicle::active()->orderBy('name')->get();
        $users = User::whereIn('role', ['lider', 'pracownik'])->orderBy('name')->get();
        $teams = Team::with('vehicle')->active()->orderBy('name')->get();
        
        // Get user's team members if user is a leader
        $leaderTeamMembers = [];
        $leaderTeam = null;
        $currentUser = Auth::user();
        if ($currentUser->isLider()) {
            $leaderTeam = Team::with('vehicle')->where('leader_id', $currentUser->id)->first();
            if ($leaderTeam && $leaderTeam->members) {
                $leaderTeamMembers = User::whereIn('id', $leaderTeam->members)->pluck('name')->toArray();
            }
        }
        
        return view('tasks.edit', compact('task', 'vehicles', 'users', 'teams', 'leaderTeamMembers', 'leaderTeam'));
    }

    public function update(Request $request, Task $task)
    {
        $this->authorize('update', $task);
        
        // Check if task is locked for current user
        if ($task->isLockedForUser(auth()->user())) {
            return redirect()->route('tasks.show', $task)
                ->with('error', 'To zadanie jest zablokowane do edycji. Status "Zaakceptowane" może być zmieniany tylko przez Administratora lub Kierownika.');
        }
        
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_datetime' => 'required|date',
            'end_datetime' => 'nullable|date|after:start_datetime',
            'vehicles' => 'required|array|min:1',
            'vehicles.*' => 'exists:vehicles,id',
            'team_id' => 'nullable|exists:teams,id',
            'team' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,jpg,png,gif,webp|max:10240',
            'status' => 'in:planned,in_progress,completed,cancelled,accepted',
        ]);

        // Check if user can set 'accepted' status
        if ($validated['status'] === 'accepted' && !$task->canSetAcceptedStatus(auth()->user())) {
            return redirect()->back()
                ->withErrors(['status' => 'Tylko Administrator i Kierownik mogą ustawiać status "Zaakceptowane".'])
                ->withInput();
        }

        // Ustaw lidera na podstawie wybranego teamu lub zachowaj obecnego
        if (isset($validated['team_id']) && $validated['team_id']) {
            // Jeśli wybrano team, ustaw lidera tego teamu jako lidera zadania
            $team = \App\Models\Team::find($validated['team_id']);
            $validated['leader_id'] = $team ? $team->leader_id : $task->leader_id;
        }
        // Jeśli team_id jest null lub pusty, zachowaj obecnego lidera (nie zmieniamy leader_id)

        $vehicleIds = $validated['vehicles'];
        unset($validated['vehicles']);
        
        // Handle image uploads
        $currentImages = $task->images ?? [];
        if ($request->hasFile('images')) {
            $uploadedImages = [];
            foreach ($request->file('images') as $image) {
                $path = $image->store('tasks', 'public');
                $uploadedImages[] = [
                    'path' => $path,
                    'original_name' => $image->getClientOriginalName(),
                    'uploaded_at' => now()->toDateTimeString()
                ];
            }
            $validated['images'] = array_merge($currentImages, $uploadedImages);
        }
        
        $task->update($validated);
        $task->vehicles()->sync($vehicleIds);

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

    public function export(Request $request)
    {
        // Only admin and kierownik can export
        if (!auth()->user()->isAdmin() && !auth()->user()->isKierownik()) {
            abort(403);
        }

        $fileName = 'zadania_' . now()->format('Y-m-d_H-i-s') . '.xlsx';
        
        return Excel::download(new TaskExport($request), $fileName);
    }

    public function removeImage(Task $task, $imageIndex)
    {
        $this->authorize('update', $task);
        
        $images = $task->images ?? [];
        
        if (isset($images[$imageIndex])) {
            // Delete file from storage
            $imagePath = $images[$imageIndex]['path'] ?? null;
            if ($imagePath && Storage::disk('public')->exists($imagePath)) {
                Storage::disk('public')->delete($imagePath);
            }
            
            // Remove from array
            array_splice($images, $imageIndex, 1);
            
            // Update task
            $task->update(['images' => $images]);
            
            return response()->json(['success' => true]);
        }
        
        return response()->json(['success' => false], 404);
    }
}