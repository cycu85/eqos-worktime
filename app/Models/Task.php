<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Model zadania
 *
 * Reprezentuje zadanie w systemie z wielodniową funkcjonalnością,
 * powiązaniami z pojazdami, liderami i zespołami.
 *
 * @property int $id
 * @property string $title
 * @property string|null $description
 * @property int|null $task_type_id
 * @property \Carbon\Carbon $start_date
 * @property \Carbon\Carbon $end_date
 * @property int $leader_id
 * @property int|null $team_id
 * @property string|null $team
 * @property string|null $notes
 * @property string $status
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 *
 * @property-read \Illuminate\Database\Eloquent\Collection<Vehicle> $vehicles
 * @property-read User $leader
 * @property-read Team|null $team
 * @property-read TaskType|null $taskType
 * @property-read \Illuminate\Database\Eloquent\Collection<TaskWorkLog> $workLogs
 * @property-read \Illuminate\Database\Eloquent\Collection<TaskAttachment> $attachments
 * @property-read float $duration_hours
 */
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

    /**
     * Powiązanie z pojazdami (wiele do wielu)
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany<Vehicle>
     */
    public function vehicles()
    {
        return $this->belongsToMany(Vehicle::class, 'task_vehicles');
    }

    /**
     * Powiązanie z liderem zadania
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<User>
     */
    public function leader()
    {
        return $this->belongsTo(User::class, 'leader_id');
    }

    /**
     * Powiązanie z zespołem
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<Team>
     */
    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Powiązanie z typem zadania
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<TaskType>
     */
    public function taskType()
    {
        return $this->belongsTo(TaskType::class);
    }

    /**
     * Powiązanie z dziennikami pracy
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<TaskWorkLog>
     */
    public function workLogs()
    {
        return $this->hasMany(TaskWorkLog::class)->orderBy('work_date');
    }

    /**
     * Powiązanie z załącznikami
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<TaskAttachment>
     */
    public function attachments()
    {
        return $this->hasMany(TaskAttachment::class)->orderBy('created_at');
    }

    /**
     * Pobierz łączną ilość godzin pracy
     *
     * @return float
     */
    public function getDurationHoursAttribute()
    {
        // Wszystkie zadania opierają się na work_logs
        return $this->getTotalWorkHours();
    }

    /**
     * Scope dla aktywnych zadań (planowane i w trakcie)
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', ['planned', 'in_progress']);
    }

    /**
     * Scope dla ukończonych zadań
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope dla zadań przypisanych do użytkownika
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $userId ID użytkownika
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('leader_id', $userId);
    }

    /**
     * Sprawdź czy zadanie jest zablokowane do edycji dla użytkownika
     *
     * Zadanie jest zablokowane gdy ma status 'accepted' a użytkownik
     * nie jest administratorem ani kierownikiem.
     *
     * @param User $user Użytkownik
     * @return bool
     */
    public function isLockedForUser($user)
    {
        // Task is locked when status is 'accepted' and user is not admin or kierownik
        return $this->status === 'accepted' && !$user->isAdmin() && !$user->isKierownik();
    }

    /**
     * Sprawdź czy użytkownik może ustawić status 'accepted'
     *
     * @param User $user Użytkownik
     * @return bool
     */
    public function canSetAcceptedStatus($user)
    {
        return $user->isAdmin() || $user->isKierownik();
    }

    /**
     * Generuj wpisy pracy dla każdego dnia zadania
     *
     * Tworzy TaskWorkLog dla każdego dnia między start_date a end_date.
     *
     * @return void
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
     *
     * @return float
     */
    public function getTotalWorkHours()
    {
        return $this->workLogs->sum(function($log) {
            return $log->getDurationHours() ?? 0;
        });
    }

    /**
     * Oblicz łączne roboczogodziny (godziny * liczba pracowników)
     *
     * @return float
     */
    public function getTotalRoboczogodziny()
    {
        $totalHours = $this->getTotalWorkHours();
        $teamSize = $this->getTeamSize();
        return $totalHours * $teamSize;
    }

    /**
     * Pobierz liczbę pracowników w zespole (włączając lidera)
     *
     * @return int
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
