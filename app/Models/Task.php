<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'start_datetime',
        'end_datetime',
        'vehicle_id',
        'leader_id',
        'team',
        'notes',
        'status',
    ];

    protected $casts = [
        'start_datetime' => 'datetime',
        'end_datetime' => 'datetime',
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function leader()
    {
        return $this->belongsTo(User::class, 'leader_id');
    }

    public function getDurationAttribute()
    {
        if (!$this->end_datetime) {
            return null;
        }
        return $this->start_datetime->diffInMinutes($this->end_datetime);
    }

    public function getDurationHoursAttribute()
    {
        $duration = $this->getDurationAttribute();
        return $duration ? round($duration / 60, 2) : null;
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['planned', 'in_progress']);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('leader_id', $userId);
    }
}
