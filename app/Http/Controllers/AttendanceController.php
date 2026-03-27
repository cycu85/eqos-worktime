<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $dateFrom = $request->get('date_from', now()->startOfMonth()->format('Y-m-d'));
        $dateTo   = $request->get('date_to', now()->format('Y-m-d'));
        $userId   = $request->get('user_id');
        $sort      = $request->get('sort', 'date');
        $direction = in_array($request->get('direction'), ['asc', 'desc'])
            ? $request->get('direction')
            : 'desc';

        // Pobierz work_logi z joinami
        $workLogs = DB::table('task_work_logs')
            ->select(
                'task_work_logs.work_date',
                'tasks.leader_id',
                'tasks.team as team_string',
                'users.name as leader_name'
            )
            ->join('tasks', 'task_work_logs.task_id', '=', 'tasks.id')
            ->join('users', 'tasks.leader_id', '=', 'users.id')
            ->whereIn('task_work_logs.status', ['in_progress', 'completed'])
            ->where('task_work_logs.work_date', '>=', $dateFrom)
            ->where('task_work_logs.work_date', '<=', $dateTo)
            ->where('users.is_active', true)
            ->get();

        // Mapa name => id dla wszystkich aktywnych użytkowników
        $usersByName = User::where('is_active', true)
            ->pluck('id', 'name');

        // Zbierz unikalne pary (user_id, work_date)
        $attendanceSet = collect();

        foreach ($workLogs as $log) {
            // Dodaj lidera
            $attendanceSet->push([
                'user_id'   => $log->leader_id,
                'user_name' => $log->leader_name,
                'work_date' => $log->work_date,
            ]);

            // Dodaj członków zespołu z pola team (string)
            if (!empty($log->team_string)) {
                $members = array_map('trim', explode(',', $log->team_string));
                foreach ($members as $memberName) {
                    if ($memberName === '') {
                        continue;
                    }
                    $memberId = $usersByName->get($memberName);
                    if ($memberId) {
                        $attendanceSet->push([
                            'user_id'   => $memberId,
                            'user_name' => $memberName,
                            'work_date' => $log->work_date,
                        ]);
                    }
                }
            }
        }

        // Deduplikacja po (user_id, work_date)
        $attendance = $attendanceSet
            ->unique(fn($row) => $row['user_id'] . '_' . $row['work_date']);

        // Filtr pracownika
        if ($userId) {
            $attendance = $attendance->filter(fn($row) => $row['user_id'] == $userId);
        }

        // Sortowanie
        if ($sort === 'name') {
            $attendance = $direction === 'asc'
                ? $attendance->sortBy('user_name')
                : $attendance->sortByDesc('user_name');
        } else {
            $attendance = $direction === 'asc'
                ? $attendance->sortBy('work_date')
                : $attendance->sortByDesc('work_date');
        }

        // Paginacja ręczna (kolekcja PHP, nie Eloquent)
        $perPage  = 20;
        $page     = $request->get('page', 1);
        $total    = $attendance->count();
        $items    = $attendance->values()->forPage($page, $perPage);

        $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $users = User::where('is_active', true)->orderBy('name')->get();

        return view('attendance.index', compact(
            'paginator', 'dateFrom', 'dateTo', 'userId',
            'sort', 'direction', 'users', 'total'
        ));
    }
}
