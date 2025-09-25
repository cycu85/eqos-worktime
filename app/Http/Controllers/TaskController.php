<?php

namespace App\Http\Controllers;

use App\Exports\TaskExport;
use App\Exports\DailyTaskExport;
use App\Models\Task;
use App\Models\TaskAttachment;
use App\Models\TaskType;
use App\Models\Team;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Kontroler do zarządzania zadaniami
 *
 * Obsługuje CRUD operacje dla zadań, filtrowanie, sortowanie,
 * eksport do Excel, zarządzanie załącznikami oraz work logs.
 */
class TaskController extends Controller
{
    /**
     * Wyświetl listę zadań z filtrowaniem i sortowaniem
     *
     * @param Request $request Żądanie HTTP zawierające parametry filtrów
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Task::class);
        
        $user = Auth::user();
        
        // Build base query
        if ($user->isAdmin() || $user->isKierownik()) {
            // Admin and Kierownik see all tasks
            $query = Task::with(['vehicles', 'leader', 'team', 'taskType', 'attachments']);
        } elseif ($user->isLider()) {
            // Lider sees tasks where they are leader OR part of the team
            $escapedName = str_replace(['%', '_'], ['\\%', '\\_'], $user->name);
            $query = Task::with(['vehicles', 'leader', 'team', 'taskType', 'attachments'])
                ->where(function ($q) use ($user, $escapedName) {
                    $q->where('leader_id', $user->id)
                      ->orWhere(function($subQuery) use ($escapedName) {
                          $subQuery->where('team', 'LIKE', $escapedName . ',%')      // Na początku
                                   ->orWhere('team', 'LIKE', '%, ' . $escapedName . ',%')  // W środku
                                   ->orWhere('team', 'LIKE', '%, ' . $escapedName)     // Na końcu
                                   ->orWhere('team', '=', $escapedName);               // Jedyny
                      });
                });
        } else {
            // Pracownik sees only tasks where they are part of the team
            $query = $user->teamTasks()->with(['vehicles', 'leader', 'team', 'taskType', 'attachments']);
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
            $user = User::active()->find($userId);
            if ($user) {
                $query->where(function ($q) use ($userId, $user) {
                    $q->where('leader_id', $userId)
                      ->orWhere('team', 'like', '%' . $user->name . '%');
                });
            }
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
        $users = User::active()->orderBy('name')->get();
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

    /**
     * Wyświetl formularz tworzenia nowego zadania
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $this->authorize('create', Task::class);
        
        $vehicles = Vehicle::active()->orderBy('name')->get();
        $users = User::active()->whereIn('role', ['lider', 'pracownik'])->orderBy('name')->get();
        $teams = Team::with('vehicles')->active()->orderBy('name')->get();
        $taskTypes = TaskType::active()->orderBy('name')->get();
        
        // Get user's team members if user is a leader
        $leaderTeamMembers = [];
        $leaderTeam = null;
        $currentUser = Auth::user();
        if ($currentUser->isLider()) {
            $leaderTeam = Team::with('vehicles')->where('leader_id', $currentUser->id)->first();
            if ($leaderTeam && $leaderTeam->members) {
                $leaderTeamMembers = User::active()->whereIn('id', $leaderTeam->members)->pluck('name')->toArray();
            }
        }
        
        return view('tasks.create', compact('vehicles', 'users', 'teams', 'taskTypes', 'leaderTeamMembers', 'leaderTeam'));
    }

    /**
     * Zapisz nowe zadanie w bazie danych
     *
     * @param Request $request Dane zadania z formularza
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $this->authorize('create', Task::class);
        
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'task_type_id' => 'nullable|exists:task_types,id',
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

    /**
     * Wyświetl dzienniki pracy dla zadania
     *
     * @param Task $task Zadanie
     * @return \Illuminate\View\View
     */
    public function workLogs(Task $task)
    {
        $this->authorize('update', $task);
        
        $task->load(['workLogs', 'vehicles', 'leader', 'team']);
        
        return view('tasks.work-logs', compact('task'));
    }

    /**
     * Wyświetl szczegóły zadania
     *
     * @param Task $task Zadanie
     * @return \Illuminate\View\View
     */
    public function show(Task $task)
    {
        $this->authorize('view', $task);
        
        $task->load(['vehicles', 'leader', 'team', 'taskType']);
        
        return view('tasks.show', compact('task'));
    }

    /**
     * Wyświetl formularz edycji zadania
     *
     * @param Task $task Zadanie
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function edit(Task $task)
    {
        $this->authorize('update', $task);
        
        // Check if task is locked for current user
        if ($task->isLockedForUser(auth()->user())) {
            return redirect()->route('tasks.show', $task)
                ->with('error', 'To zadanie jest zablokowane do edycji. Status "Zaakceptowane" może być zmieniany tylko przez Administratora lub Kierownika.');
        }
        
        $vehicles = Vehicle::active()->orderBy('name')->get();
        $users = User::active()->whereIn('role', ['lider', 'pracownik'])->orderBy('name')->get();
        $teams = Team::with('vehicles')->active()->orderBy('name')->get();
        $taskTypes = TaskType::active()->orderBy('name')->get();
        
        // Get user's team members if user is a leader
        $leaderTeamMembers = [];
        $leaderTeam = null;
        $currentUser = Auth::user();
        if ($currentUser->isLider()) {
            $leaderTeam = Team::with('vehicles')->where('leader_id', $currentUser->id)->first();
            if ($leaderTeam && $leaderTeam->members) {
                $leaderTeamMembers = User::active()->whereIn('id', $leaderTeam->members)->pluck('name')->toArray();
            }
        }
        
        return view('tasks.edit', compact('task', 'vehicles', 'users', 'teams', 'taskTypes', 'leaderTeamMembers', 'leaderTeam'));
    }

    /**
     * Zaktualizuj zadanie w bazie danych
     *
     * @param Request $request Dane zadania z formularza
     * @param Task $task Zadanie do aktualizacji
     * @return \Illuminate\Http\RedirectResponse
     */
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
            'task_type_id' => 'nullable|exists:task_types,id',
            'vehicles' => 'required|array|min:1',
            'vehicles.*' => 'exists:vehicles,id',
            'team_id' => 'nullable|exists:teams,id',
            'team' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|mimes:jpeg,jpg,png,gif,webp,pdf,doc,docx,xls,xlsx,txt|max:10240',
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
        
        // Handle attachment uploads - używamy nowej tabeli task_attachments
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('tasks', 'public');
                
                // Utwórz nowy załącznik w tabeli task_attachments
                $task->attachments()->create([
                    'file_path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'file_size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                    'uploaded_by' => auth()->id(),
                ]);
            }
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
     * Aktualizuj status work_logs gdy zmieni się status zadania
     *
     * @param Task $task Zadanie
     * @param string $oldStatus Poprzedni status
     * @param string $newStatus Nowy status
     * @return void
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

    /**
     * Usuń zadanie z bazy danych
     *
     * @param Task $task Zadanie do usunięcia
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Task $task)
    {
        $this->authorize('delete', $task);
        
        $task->delete();

        return redirect()->route('tasks.index')
            ->with('success', 'Zadanie zostało usunięte pomyślnie.');
    }

    /**
     * Eksportuj zadania do pliku Excel
     *
     * @param Request $request Parametry eksportu
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function export(Request $request)
    {
        $this->authorize('export', Task::class);

        $fileName = 'zadania_' . now()->format('Y-m-d_H-i-s') . '.xlsx';
        
        return Excel::download(new TaskExport($request), $fileName);
    }

    /**
     * Eksportuj raport dzienny do pliku Excel
     *
     * @param Request $request Parametry eksportu
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function exportDaily(Request $request)
    {
        $this->authorize('export', Task::class);
        
        $fileName = 'raport_dzienny_' . now()->format('Y-m-d_H-i-s') . '.xlsx';
        
        return Excel::download(new DailyTaskExport($request), $fileName);
    }

    /**
     * Usuń załącznik z zadania
     *
     * @param Task $task Zadanie
     * @param TaskAttachment $attachment Załącznik do usunięcia
     * @return \Illuminate\Http\JsonResponse
     */
    public function removeAttachment(Task $task, TaskAttachment $attachment)
    {
        $this->authorize('update', $task);
        
        // $attachment jest już dostępny jako parameter dzięki route model binding
        
        if ($attachment) {
            // Delete file from storage
            if (Storage::disk('public')->exists($attachment->file_path)) {
                Storage::disk('public')->delete($attachment->file_path);
            }
            
            // Delete attachment record
            $attachment->delete();
            
            return response()->json(['success' => true]);
        }
        
        return response()->json(['success' => false], 404);
    }
}