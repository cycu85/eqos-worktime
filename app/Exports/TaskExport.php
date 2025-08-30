<?php

namespace App\Exports;

use App\Models\Task;
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
        
        // Build base query - same as in TaskController
        if ($user->isAdmin() || $user->isKierownik()) {
            $query = Task::with(['vehicle', 'leader', 'team']);
        } elseif ($user->isLider()) {
            $query = Task::with(['vehicle', 'leader', 'team'])->forUser($user->id);
        } else {
            $query = $user->teamTasks()->with(['vehicle', 'leader', 'team']);
        }
        
        // Apply same filters as in TaskController
        if ($this->request->filled('search')) {
            $search = $this->request->get('search');
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
        
        if ($this->request->filled('status')) {
            $query->where('status', $this->request->get('status'));
        }
        
        if ($this->request->filled('vehicle_id')) {
            $query->where('vehicle_id', $this->request->get('vehicle_id'));
        }
        
        if ($this->request->filled('date_from')) {
            $query->where('start_datetime', '>=', $this->request->get('date_from'));
        }
        
        if ($this->request->filled('date_to')) {
            $query->where('start_datetime', '<=', $this->request->get('date_to') . ' 23:59:59');
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
        
        $allowedSorts = ['title', 'start_datetime', 'status', 'created_at'];
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
            $task->vehicle ? $task->vehicle->name : '',
            $task->vehicle ? $task->vehicle->registration : '',
            $task->leader ? $task->leader->name : '',
            $task->team,
            $this->getStatusLabel($task->status),
            $task->start_datetime ? $task->start_datetime->format('Y-m-d H:i') : '',
            $task->end_datetime ? $task->end_datetime->format('Y-m-d H:i') : '',
            $this->calculateDuration($task->start_datetime, $task->end_datetime),
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

    private function calculateDuration($startDateTime, $endDateTime)
    {
        if (!$startDateTime || !$endDateTime) {
            return '';
        }

        $start = \Carbon\Carbon::parse($startDateTime);
        $end = \Carbon\Carbon::parse($endDateTime);
        
        $diffInMinutes = $start->diffInMinutes($end);
        $hours = intval($diffInMinutes / 60);
        $minutes = $diffInMinutes % 60;
        
        if ($hours > 0) {
            return sprintf('%dh %dmin', $hours, $minutes);
        } else {
            return sprintf('%dmin', $minutes);
        }
    }
}