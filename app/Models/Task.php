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
        'task_type_id',
        'start_date',
        'end_date',
        'leader_id',
        'team_id',
        'team',
        'notes',
        'status',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
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

    public function taskType()
    {
        return $this->belongsTo(TaskType::class);
    }

    public function workLogs()
    {
        return $this->hasMany(TaskWorkLog::class)->orderBy('work_date');
    }

    public function attachments()
    {
        return $this->hasMany(TaskAttachment::class)->orderBy('created_at');
    }

    public function getDurationHoursAttribute()
    {
        // Wszystkie zadania opierają się na work_logs
        return $this->getTotalWorkHours();
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

    /**
     * Generuj wpisy pracy dla każdego dnia zadania
     */
    public function generateWorkLogs()
    {
        if (!$this->start_date || !$this->end_date) {
            return;
        }

        $period = new \DatePeriod(
            new \DateTime($this->start_date->format('Y-m-d')),
            new \DateInterval('P1D'),
            new \DateTime($this->end_date->format('Y-m-d') . ' +1 day')
        );

        foreach ($period as $date) {
            TaskWorkLog::firstOrCreate([
                'task_id' => $this->id,
                'work_date' => $date->format('Y-m-d')
            ], [
                'start_time' => '07:00',
                'end_time' => '18:00',
                'status' => 'planned'
            ]);
        }
    }

    /**
     * Oblicz łączne godziny pracy ze wszystkich work_logs
     */
    public function getTotalWorkHours()
    {
        return $this->workLogs->sum(function($log) {
            return $log->getDurationHours() ?? 0;
        });
    }

    /**
     * Oblicz łączne roboczogodziny (godziny * liczba pracowników)
     */
    public function getTotalRoboczogodziny()
    {
        $totalHours = $this->getTotalWorkHours();
        $teamSize = $this->getTeamSize();
        return $totalHours * $teamSize;
    }

    /**
     * Pobierz liczbę pracowników w zespole (włączając lidera)
     */
    public function getTeamSize()
    {
        $teamMembers = 0;
        
        if ($this->team) {
            // Jeśli team to string z nazwami oddzielone przecinkami
            $teamMembers = count(explode(',', trim($this->team)));
        }
        
        // Zawsze dodaj lidera (lider + członkowie zespołu)
        return $teamMembers + 1;
    }
}
