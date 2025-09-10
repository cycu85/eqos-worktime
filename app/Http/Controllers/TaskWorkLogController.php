<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskWorkLog;
use Illuminate\Http\Request;

class TaskWorkLogController extends Controller
{
    public function bulkUpdate(Request $request, Task $task)
    {
        $this->authorize('update', $task);
        
        $validated = $request->validate([
            'logs' => 'required|array',
            'logs.*.start_time' => 'required|date_format:H:i',
            'logs.*.end_time' => 'required|date_format:H:i|after:logs.*.start_time',
            'logs.*.status' => 'required|in:planned,in_progress,completed,cancelled',
            'logs.*.notes' => 'nullable|string',
            'logs.*.completed_tasks_count' => 'nullable|integer|min:0|max:99',
        ]);

        foreach ($validated['logs'] as $logId => $logData) {
            // Dodaj sekundy do czasu jeśli nie ma
            if (isset($logData['start_time']) && strlen($logData['start_time']) === 5) {
                $logData['start_time'] .= ':00';
            }
            if (isset($logData['end_time']) && strlen($logData['end_time']) === 5) {
                $logData['end_time'] .= ':00';
            }
            
            TaskWorkLog::where('id', $logId)
                ->where('task_id', $task->id)
                ->update($logData);
        }

        return back()->with('success', 'Harmonogram pracy został zaktualizowany.');
    }

    public function destroy(Task $task, TaskWorkLog $workLog)
    {
        $this->authorize('update', $task);
        
        // Sprawdź czy work_log należy do tego zadania
        if ($workLog->task_id !== $task->id) {
            abort(404);
        }

        // Nie pozwalaj usunąć ostatniego dnia
        if ($task->workLogs()->count() <= 1) {
            return back()->with('error', 'Nie można usunąć ostatniego dnia pracy. Zadanie musi mieć co najmniej jeden dzień.');
        }

        $workLog->delete();

        return back()->with('success', 'Dzień pracy został usunięty.');
    }

    public function addWorkDay(Request $request, Task $task)
    {
        $this->authorize('update', $task);
        
        $validated = $request->validate([
            'work_date' => 'required|date|after_or_equal:today'
        ]);

        $selectedDate = \Carbon\Carbon::parse($validated['work_date']);

        // Sprawdź czy taki dzień już nie istnieje
        if ($task->workLogs()->where('work_date', $selectedDate->format('Y-m-d'))->exists()) {
            return back()->with('error', 'Dzień pracy na wybraną datę już istnieje.');
        }

        // Utwórz nowy wpis
        TaskWorkLog::create([
            'task_id' => $task->id,
            'work_date' => $selectedDate->format('Y-m-d'),
            'start_time' => '07:00:00',
            'end_time' => '18:00:00',
            'status' => 'planned'
        ]);

        // Zaktualizuj end_date zadania jeśli nowa data jest późniejsza
        if ($selectedDate->gt($task->end_date)) {
            $task->update(['end_date' => $selectedDate]);
        }

        return back()->with('success', 'Nowy dzień pracy został dodany: ' . $selectedDate->format('d.m.Y'));
    }
}
