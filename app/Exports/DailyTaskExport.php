<?php

namespace App\Exports;

use App\Models\Task;
use App\Models\TaskWorkLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DailyTaskExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function collection()
    {
        $user = Auth::user();
        
        // Build base query - same as in TaskController
        if ($user->isAdmin() || $user->isKierownik()) {
            $query = Task::with(['vehicles', 'leader', 'team', 'taskType', 'workLogs']);
        } elseif ($user->isLider()) {
            $query = Task::with(['vehicles', 'leader', 'team', 'taskType', 'workLogs'])->forUser($user->id);
        } else {
            $query = $user->teamTasks()->with(['vehicles', 'leader', 'team', 'taskType', 'workLogs']);
        }
        
        // Apply same filters as in TaskController
        if ($this->request->filled('search')) {
            $search = $this->request->get('search');
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
        
        if ($this->request->filled('status')) {
            $query->where('status', $this->request->get('status'));
        }
        
        if ($this->request->filled('vehicle_id')) {
            $query->whereHas('vehicles', function ($q) {
                $q->where('vehicles.id', $this->request->get('vehicle_id'));
            });
        }
        
        if ($this->request->filled('date_from')) {
            $query->where('start_date', '>=', $this->request->get('date_from'));
        }
        
        if ($this->request->filled('date_to')) {
            $query->where('start_date', '<=', $this->request->get('date_to'));
        }
        
        if ($this->request->filled('user_id')) {
            $userId = $this->request->get('user_id');
            $query->where(function ($q) use ($userId) {
                $q->where('leader_id', $userId)
                  ->orWhere('team', 'like', '%' . User::find($userId)->name . '%');
            });
        }
        
        // Get tasks and expand to work logs
        $tasks = $query->get();
        $workLogs = collect();
        
        foreach ($tasks as $task) {
            foreach ($task->workLogs as $workLog) {
                $workLogs->push($workLog);
            }
        }
        
        // Sort work logs by date
        return $workLogs->sortBy('work_date');
    }

    public function headings(): array
    {
        return [
            'ID Zadania',
            'Tytuł zadania',
            'Rodzaj zadania',
            'Data pracy',
            'Dzień tygodnia',
            'Godzina rozpoczęcia',
            'Godzina zakończenia', 
            'Czas trwania (godziny)',
            'Ilość wykonanych zadań',
            'Status dnia',
            'Lider zespołu',
            'Skład zespołu (obecni)',
            'Nieobecni w zespole',
            'Pojazdy - nazwa',
            'Pojazdy - rejestracja',
            'Notatki dnia',
            'Notatki zadania'
        ];
    }

    public function map($workLog): array
    {
        $task = $workLog->task;
        
        return [
            $task->id,
            $task->title,
            $task->taskType ? $task->taskType->name : '',
            $workLog->work_date->format('Y-m-d'),
            $workLog->work_date->locale('pl')->isoFormat('dddd'),
            substr($workLog->start_time, 0, 5),
            substr($workLog->end_time, 0, 5),
            $workLog->getDurationHours() ?? 0,
            $workLog->completed_tasks_count ?? 0,
            $this->getWorkLogStatusLabel($workLog->status),
            $task->leader ? $task->leader->name : '',
            $this->getEffectiveTeamForDate($task, $workLog->work_date),
            $this->getAbsentTeamMembersForDate($task, $workLog->work_date),
            $task->vehicles->count() > 0 ? $task->vehicles->pluck('name')->join(', ') : '',
            $task->vehicles->count() > 0 ? $task->vehicles->pluck('registration')->join(', ') : '',
            $workLog->notes ?: '',
            $task->notes ?: ''
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    private function getWorkLogStatusLabel($status)
    {
        return match($status) {
            'planned' => 'Planowane',
            'in_progress' => 'W trakcie',
            'completed' => 'Ukończone',
            'cancelled' => 'Anulowane',
            default => $status
        };
    }

    /**
     * Pobierz efektywny skład zespołu dla danej daty (bez nieobecnych)
     *
     * @param Task $task
     * @param Carbon $date
     * @return string
     */
    private function getEffectiveTeamForDate($task, $date)
    {
        $presentMembers = [];

        // Sprawdź lidera
        if ($task->leader && !$task->leader->isAbsentOn($date)) {
            $presentMembers[] = $task->leader->name;
        }

        // Sprawdź członków zespołu
        if ($task->team) {
            $teamMemberNames = array_map('trim', explode(',', $task->team));
            foreach ($teamMemberNames as $memberName) {
                $user = User::where('name', $memberName)->first();
                if ($user && !$user->isAbsentOn($date)) {
                    // Sprawdź czy lider nie jest już dodany
                    if (!$task->leader || $task->leader->name !== $memberName) {
                        $presentMembers[] = $memberName;
                    }
                }
            }
        }

        return implode(', ', $presentMembers);
    }

    /**
     * Pobierz listę nieobecnych członków zespołu dla danej daty
     *
     * @param Task $task
     * @param Carbon $date
     * @return string
     */
    private function getAbsentTeamMembersForDate($task, $date)
    {
        $absentMembers = [];

        // Sprawdź lidera
        if ($task->leader && $task->leader->isAbsentOn($date)) {
            $absentMembers[] = $task->leader->name . ' (lider)';
        }

        // Sprawdź członków zespołu
        if ($task->team) {
            $teamMemberNames = array_map('trim', explode(',', $task->team));
            foreach ($teamMemberNames as $memberName) {
                $user = User::where('name', $memberName)->first();
                if ($user && $user->isAbsentOn($date)) {
                    // Sprawdź czy lider nie jest już dodany
                    if (!$task->leader || $task->leader->name !== $memberName) {
                        $absentMembers[] = $memberName;
                    }
                }
            }
        }

        return implode(', ', $absentMembers);
    }
}