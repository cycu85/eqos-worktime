<?php

namespace App\Exports;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class FinanceExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function collection()
    {
        $dateFrom = $this->request->get('date_from', now()->startOfMonth()->format('Y-m-d'));
        $dateTo = $this->request->get('date_to', now()->format('Y-m-d'));
        $taskTypeId = $this->request->get('task_type_id');
        $teamId = $this->request->get('team_id');

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

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'Data',
            'Zespół',
            'Rodzaj zadania',
            'Ilość zadań',
            'Wartość za szt. (zł)',
            'Suma (zł)',
        ];
    }

    public function map($row): array
    {
        return [
            $row->work_date,
            $row->team_name,
            $row->task_type_name,
            $row->total_count,
            number_format($row->unit_value, 2, ',', ' '),
            number_format($row->total_value, 2, ',', ' '),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
