<?php

namespace App\Http\Controllers;

use App\Exports\FinanceExport;
use App\Models\TaskType;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class FinanceController extends Controller
{
    public function index(Request $request)
    {
        $dateFrom = $request->get('date_from', now()->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->get('date_to', now()->format('Y-m-d'));
        $taskTypeId = $request->get('task_type_id');
        $teamId = $request->get('team_id');

        // Podzapytanie do znalezienia aktualnej ceny dla każdego task_type_id i work_date
        $priceSubquery = DB::table('task_type_prices as ttp')
            ->select('ttp.price')
            ->whereColumn('ttp.task_type_id', 'tasks.task_type_id')
            ->whereColumn('ttp.valid_from', '<=', 'task_work_logs.work_date')
            ->orderByDesc('ttp.valid_from')
            ->limit(1);

        $query = DB::table('task_work_logs')
            ->select(
                'task_work_logs.work_date',
                DB::raw("COALESCE(teams.name, 'Brak zespołu') as team_name"),
                'task_types.name as task_type_name',
                'tasks.task_type_id',
                DB::raw('SUM(COALESCE(task_work_logs.completed_tasks_count, 0)) as total_count'),
                DB::raw('COALESCE((' . $priceSubquery->toSql() . '), 0) as unit_value'),
                DB::raw('SUM(COALESCE(task_work_logs.completed_tasks_count, 0)) * COALESCE((' . $priceSubquery->toSql() . '), 0) as total_value')
            )
            ->join('tasks', 'task_work_logs.task_id', '=', 'tasks.id')
            ->join('task_types', 'tasks.task_type_id', '=', 'task_types.id')
            ->leftJoin('teams', 'tasks.team_id', '=', 'teams.id')
            ->where('task_work_logs.work_date', '>=', $dateFrom)
            ->where('task_work_logs.work_date', '<=', $dateTo)
            ->groupBy(
                'task_work_logs.work_date',
                'tasks.team_id',
                'tasks.task_type_id',
                'teams.name',
                'task_types.name'
            )
            ->havingRaw('SUM(COALESCE(task_work_logs.completed_tasks_count, 0)) > 0')
            ->orderByDesc('task_work_logs.work_date');

        if ($taskTypeId) {
            $query->where('tasks.task_type_id', $taskTypeId);
        }

        if ($teamId) {
            $query->where('tasks.team_id', $teamId);
        }

        $data = $query->get();

        $grandTotalCount = $data->sum('total_count');
        $grandTotalValue = $data->sum('total_value');

        $taskTypes = TaskType::orderBy('name')->get();
        $teams = Team::orderBy('name')->get();

        return view('finanse.index', compact(
            'data', 'dateFrom', 'dateTo', 'taskTypeId', 'teamId',
            'taskTypes', 'teams', 'grandTotalCount', 'grandTotalValue'
        ));
    }

    public function export(Request $request)
    {
        $fileName = 'finanse_' . now()->format('Y-m-d_H-i-s') . '.xlsx';

        return Excel::download(new FinanceExport($request), $fileName);
    }
}
