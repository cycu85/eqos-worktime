<?php

namespace App\Exports;

use App\Models\Task;
use App\Models\TaskWorkLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TaskExport implements FromQuery, WithHeadings, WithMapping, WithStyles
{
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function query()
    {
        $user = Auth::user();
        
        // Build base query - same as in TaskController, now with workLogs
        if ($user->isAdmin() || $user->isKierownik()) {
            $query = Task::with(['vehicles', 'leader', 'team', 'workLogs']);
        } elseif ($user->isLider()) {
            $query = Task::with(['vehicles', 'leader', 'team', 'workLogs'])->forUser($user->id);
        } else {
            $query = $user->teamTasks()->with(['vehicles', 'leader', 'team', 'workLogs']);
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
        
        // Apply sorting
        $sortBy = $this->request->get('sort', 'title');
        $sortOrder = $this->request->get('direction', 'asc');
        
        $allowedSorts = ['title', 'start_date', 'status', 'created_at'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderBy('title', 'asc');
        }

        return $query;
    }

    public function headings(): array
    {
        return [
            'ID',
            'Tytuł',
            'Opis',
            'Pojazd',
            'Rejestracja',
            'Lider',
            'Zespół',
            'Status',
            'Data rozpoczęcia',
            'Data zakończenia',
            'Czas',
            'Roboczogodziny',
            'Notatki',
            'Utworzono'
        ];
    }

    public function map($task): array
    {
        return [
            $task->id,
            $task->title,
            $task->description,
            $task->vehicles->count() > 0 ? $task->vehicles->pluck('name')->join(', ') : '',
            $task->vehicles->count() > 0 ? $task->vehicles->pluck('registration')->join(', ') : '',
            $task->leader ? $task->leader->name : '',
            $task->team,
            $this->getStatusLabel($task->status),
            $task->start_date ? $task->start_date->format('Y-m-d') : '',
            $task->end_date ? $task->end_date->format('Y-m-d') : '',
            $this->calculateDurationFromWorkLogs($task),
            $this->calculateWorkHours($task),
            $task->notes,
            $task->created_at->format('Y-m-d H:i')
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    private function getStatusLabel($status)
    {
        return match($status) {
            'planned' => 'Planowane',
            'in_progress' => 'W trakcie',
            'completed' => 'Ukończone',
            'cancelled' => 'Anulowane',
            default => $status
        };
    }

    private function calculateDurationFromWorkLogs($task)
    {
        if ($task->workLogs->isEmpty()) {
            return '';
        }

        $totalHours = $task->getTotalWorkHours();
        $hours = intval($totalHours);
        $minutes = intval(($totalHours - $hours) * 60);
        
        if ($hours > 0) {
            return sprintf('%dh %dmin', $hours, $minutes);
        } else {
            return sprintf('%dmin', $minutes);
        }
    }

    private function calculateWorkHours($task)
    {
        if ($task->workLogs->isEmpty()) {
            return '';
        }

        // Używamy metod z modelu Task które już obliczają na podstawie work_logs
        $roboczogodziny = $task->getTotalRoboczogodziny();

        return round($roboczogodziny, 2);
    }
}