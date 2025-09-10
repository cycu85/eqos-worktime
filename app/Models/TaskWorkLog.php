<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskWorkLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_id',
        'work_date',
        'start_time',
        'end_time',
        'actual_start_datetime',
        'actual_end_datetime',
        'notes',
        'completed_tasks_count',
        'status',
    ];

    protected $casts = [
        'work_date' => 'date',
        'actual_start_datetime' => 'datetime',
        'actual_end_datetime' => 'datetime',
    ];

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function getDurationInMinutes()
    {
        if ($this->actual_start_datetime && $this->actual_end_datetime) {
            return $this->actual_start_datetime->diffInMinutes($this->actual_end_datetime);
        }
        
        if ($this->start_time && $this->end_time) {
            // start_time i end_time to stringi w formacie HH:MM:SS
            $start = Carbon::createFromFormat('H:i:s', $this->start_time);
            $end = Carbon::createFromFormat('H:i:s', $this->end_time);
            return $start->diffInMinutes($end);
        }
        
        return null;
    }

    public function getDurationHours()
    {
        $minutes = $this->getDurationInMinutes();
        return $minutes ? round($minutes / 60, 2) : null;
    }
}
