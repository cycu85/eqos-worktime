<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Model nieobecności użytkownika
 *
 * Reprezentuje okresy nieobecności pracowników z możliwością zatwierdzania
 * przez kierowników i administratorów.
 *
 * @property int $id
 * @property int $user_id
 * @property \Carbon\Carbon $start_date
 * @property \Carbon\Carbon $end_date
 * @property string $type
 * @property string|null $description
 * @property string $status
 * @property int|null $approved_by
 * @property \Carbon\Carbon|null $approved_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 *
 * @property-read User $user
 * @property-read User|null $approver
 */
class UserAbsence extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'start_date',
        'end_date',
        'type',
        'description',
        'status',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'approved_at' => 'datetime',
    ];

    /**
     * Powiązanie z użytkownikiem
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<User>
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Powiązanie z użytkownikiem zatwierdzającym
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<User>
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Scope dla zatwierdzonych nieobecności
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'zatwierdzona');
    }

    /**
     * Scope dla oczekujących nieobecności
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePending($query)
    {
        return $query->where('status', 'oczekujaca');
    }

    /**
     * Scope dla nieobecności w danym okresie
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $startDate
     * @param string $endDate
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeInPeriod($query, $startDate, $endDate)
    {
        return $query->where(function ($q) use ($startDate, $endDate) {
            $q->whereBetween('start_date', [$startDate, $endDate])
              ->orWhereBetween('end_date', [$startDate, $endDate])
              ->orWhere(function ($subQ) use ($startDate, $endDate) {
                  $subQ->where('start_date', '<=', $startDate)
                       ->where('end_date', '>=', $endDate);
              });
        });
    }

    /**
     * Sprawdź czy nieobecność zawiera podaną datę
     *
     * @param string $date
     * @return bool
     */
    public function containsDate($date)
    {
        return $this->start_date <= $date && $this->end_date >= $date;
    }

    /**
     * Pobierz liczbę dni nieobecności
     *
     * @return int
     */
    public function getDaysCount()
    {
        return $this->start_date->diffInDays($this->end_date) + 1;
    }

    /**
     * Sprawdź czy nieobecność może być edytowana przez użytkownika
     *
     * @param User $user
     * @return bool
     */
    public function canBeEditedBy($user)
    {
        // Admin i kierownik mogą edytować wszystkie
        if ($user->isAdmin() || $user->isKierownik()) {
            return true;
        }

        // Właściciel może edytować tylko oczekujące
        return $this->user_id === $user->id && $this->status === 'oczekujaca';
    }

    /**
     * Sprawdź czy nieobecność może być zatwierdzona przez użytkownika
     *
     * @param User $user
     * @return bool
     */
    public function canBeApprovedBy($user)
    {
        return ($user->isAdmin() || $user->isKierownik())
               && $this->status === 'oczekujaca'
               && $this->user_id !== $user->id;
    }
}
