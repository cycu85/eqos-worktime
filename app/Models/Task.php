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
        'leader_id',
        'team_id',
        'team',
        'notes',
        'images',
        'status',
    ];

    protected $casts = [
        'start_datetime' => 'datetime',
        'end_datetime' => 'datetime',
        'images' => 'array',
    ];

    public function vehicles()
    {
        return $this->belongsToMany(Vehicle::class, 'task_vehicles');
    }

    public function leader()
    {
        return $this->belongsTo(User::class, 'leader_id');
    }

    public function team()
    {
        return $this->belongsTo(Team::class);
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

    /**
     * Check if task is locked for editing by non-admin/kierownik users
     */
    public function isLockedForUser($user)
    {
        // Task is locked when status is 'accepted' and user is not admin or kierownik
        return $this->status === 'accepted' && !$user->isAdmin() && !$user->isKierownik();
    }

    /**
     * Check if task status can be changed to 'accepted' by user
     */
    public function canSetAcceptedStatus($user)
    {
        return $user->isAdmin() || $user->isKierownik();
    }
}
