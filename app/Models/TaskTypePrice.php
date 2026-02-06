<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskTypePrice extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_type_id',
        'price',
        'valid_from',
    ];

    protected $casts = [
        'price' => 'float',
        'valid_from' => 'date',
    ];

    public function taskType()
    {
        return $this->belongsTo(TaskType::class);
    }

    /**
     * Pobiera aktualną cenę dla danego typu zadania na określony dzień
     */
    public static function getPriceForDate($taskTypeId, $date)
    {
        return self::where('task_type_id', $taskTypeId)
            ->where('valid_from', '<=', $date)
            ->orderByDesc('valid_from')
            ->first();
    }
}
