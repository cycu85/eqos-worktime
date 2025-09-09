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
            // Lider sees tasks where they are leader OR part of the team
            $query = Task::with(['vehicles', 'leader', 'team'])
                ->where(function ($q) use ($user) {
                    $q->where('leader_id', $user->id)
                      ->orWhereRaw("FIND_IN_SET(?, REPLACE(team, ', ', ','))", [$user->name]);
                });
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
            $query->where('start_date', '>=', $request->get('date_from'));
        }
        
        if ($request->filled('date_to')) {
            $query->where('start_date', '<=', $request->get('date_to'));
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
        
        $allowedSorts = ['title', 'start_date', 'status', 'created_at'];
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
        
        // Prepare tasks data for JavaScript calendar - now using work_logs dates
        $calendarTasks = collect();
        
        foreach ($allTasks as $task) {
            // Create calendar entry for each work log day
            foreach ($task->workLogs as $workLog) {
                $calendarTasks->push([
                    'id' => $task->id,
                    'work_log_id' => $workLog->id,
                    'title' => $task->title,
                    'start_datetime' => $workLog->work_date->format('Y-m-d') . ' ' . $workLog->start_time,
                    'end_datetime' => $workLog->work_date->format('Y-m-d') . ' ' . $workLog->end_time,
                    'work_date' => $workLog->work_date->format('Y-m-d'),
                    'start_time' => substr($workLog->start_time, 0, 5), // HH:MM format
                    'end_time' => substr($workLog->end_time, 0, 5),
                    'duration_hours' => $workLog->getDurationHours() ?? 0,
                    'status' => $workLog->status,
                    'task_status' => $task->status,
                    'leader' => $task->leader->name,
                    'vehicles' => $task->vehicles->pluck('name')->join(', '),
                    'notes' => $workLog->notes,
                    'url' => route('tasks.show', $task),
                    'work_logs_url' => route('tasks.work-logs', $task)
                ]);
            }
        }
        
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
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'vehicles' => 'required|array|min:1',
            'vehicles.*' => 'exists:vehicles,id',
            'team_id' => 'nullable|exists:teams,id',
            'team' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'status' => 'in:planned,in_progress,completed,cancelled',
        ]);

        // Ustaw lidera na podstawie wybranego teamu lub aktualnego użytkownika
        $teamId = $validated['team_id'] ?? null;
        if ($teamId) {
            // Jeśli wybrano team, ustaw lidera tego teamu jako lidera zadania
            $team = \App\Models\Team::find($teamId);
            $validated['leader_id'] = $team ? $team->leader_id : Auth::id();
        } else {
            // Jeśli nie wybrano teamu, aktualny użytkownik staje się liderem
            $validated['leader_id'] = Auth::id();
        }
        
        $vehicleIds = $validated['vehicles'];
        unset($validated['vehicles'], $validated['team_id']);

        $task = Task::create($validated);
        $task->vehicles()->attach($vehicleIds);

        // Wygeneruj work_logs dla każdego dnia
        $task->generateWorkLogs();

        return redirect()->route('tasks.show', $task)
            ->with('success', 'Zadanie wielodniowe zostało utworzone pomyślnie.');
    }

    public function workLogs(Task $task)
    {
        $this->authorize('update', $task);
        
        $task->load(['workLogs', 'vehicles', 'leader', 'team']);
        
        return view('tasks.work-logs', compact('task'));
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
        
        $validationRules = [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'vehicles' => 'required|array|min:1',
            'vehicles.*' => 'exists:vehicles,id',
            'team_id' => 'nullable|exists:teams,id',
            'team' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,jpg,png,gif,webp|max:10240',
            'status' => 'in:planned,in_progress,completed,cancelled,accepted',
        ];

        // Only admin and kierownik can edit dates
        if (auth()->user()->isAdmin() || auth()->user()->isKierownik()) {
            $validationRules['start_date'] = 'required|date';
            $validationRules['end_date'] = 'required|date|after_or_equal:start_date';
        }

        $validated = $request->validate($validationRules);

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
        
        // Check if dates have changed (only for admin/kierownik)
        $datesChanged = false;
        if ((auth()->user()->isAdmin() || auth()->user()->isKierownik()) && 
            isset($validated['start_date']) && isset($validated['end_date'])) {
            $newStartDate = \Carbon\Carbon::parse($validated['start_date']);
            $newEndDate = \Carbon\Carbon::parse($validated['end_date']);
            
            $datesChanged = !$task->start_date->isSameDay($newStartDate) || 
                           !$task->end_date->isSameDay($newEndDate);
        }

        // Check if status has changed
        $statusChanged = $task->status !== $validated['status'];
        $oldStatus = $task->status;
        $newStatus = $validated['status'];

        $task->update($validated);
        $task->vehicles()->sync($vehicleIds);

        // Regenerate work logs if dates changed
        if ($datesChanged) {
            // Remove existing work logs
            $task->workLogs()->delete();
            // Generate new work logs
            $task->generateWorkLogs();
        }

        // Update work_logs status if task status changed
        if ($statusChanged) {
            $this->updateWorkLogsStatus($task, $oldStatus, $newStatus);
        }

        return redirect()->route('tasks.show', $task)
            ->with('success', 'Zadanie zostało zaktualizowane pomyślnie.');
    }

    /**
     * Update work_logs status when task status changes
     */
    private function updateWorkLogsStatus(Task $task, string $oldStatus, string $newStatus)
    {
        // Define mapping from task status to work_log status
        $statusMapping = [
            'planned' => 'planned',
            'in_progress' => 'in_progress',
            'completed' => 'completed',
            'cancelled' => 'cancelled',
            'accepted' => 'completed' // accepted tasks have completed work_logs
        ];

        $workLogStatus = $statusMapping[$newStatus] ?? 'planned';

        // Update all work_logs for this task
        $task->workLogs()->update(['status' => $workLogStatus]);

        // Special logic for different status changes
        switch ($newStatus) {
            case 'in_progress':
                // When task is in progress, mark past days as completed and future days as planned
                $today = now()->format('Y-m-d');
                
                $task->workLogs()
                    ->where('work_date', '<', $today)
                    ->update(['status' => 'completed']);
                    
                $task->workLogs()
                    ->where('work_date', '>=', $today)
                    ->update(['status' => 'in_progress']);
                break;
                
            case 'completed':
            case 'accepted':
                // All work_logs should be completed
                $task->workLogs()->update(['status' => 'completed']);
                break;
                
            case 'cancelled':
                // All work_logs should be cancelled
                $task->workLogs()->update(['status' => 'cancelled']);
                break;
                
            case 'planned':
                // All work_logs should be planned
                $task->workLogs()->update(['status' => 'planned']);
                break;
        }
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