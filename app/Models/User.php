<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Model użytkownika
 *
 * Rozszerza podstawowy model uwierzytelniania o role-based access control
 * i powiązania z zadaniami.
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property \Carbon\Carbon|null $email_verified_at
 * @property string $password
 * @property string $role Rola: admin, kierownik, lider, pracownik, ksiegowy
 * @property \Carbon\Carbon|null $last_login_at
 * @property string|null $remember_token
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 *
 * @property-read \Illuminate\Database\Eloquent\Collection<Task> $tasks
 */
class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
        'last_login_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Sprawdź czy użytkownik jest administratorem
     *
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Sprawdź czy użytkownik jest kierownikiem
     *
     * @return bool
     */
    public function isKierownik(): bool
    {
        return $this->role === 'kierownik';
    }

    /**
     * Sprawdź czy użytkownik jest liderem
     *
     * @return bool
     */
    public function isLider(): bool
    {
        return $this->role === 'lider';
    }

    /**
     * Sprawdź czy użytkownik jest pracownikiem
     *
     * @return bool
     */
    public function isPracownik(): bool
    {
        return $this->role === 'pracownik';
    }

    /**
     * Sprawdź czy użytkownik jest księgowym
     *
     * @return bool
     */
    public function isKsiegowy(): bool
    {
        return $this->role === 'ksiegowy';
    }

    /**
     * Sprawdź czy użytkownik jest aktywny
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * Scope dla aktywnych użytkowników
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope dla nieaktywnych użytkowników
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    /**
     * Powiązanie z zadaniami gdzie użytkownik jest liderem
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<Task>
     */
    public function tasks()
    {
        return $this->hasMany(Task::class, 'leader_id');
    }

    /**
     * Pobierz zadania gdzie użytkownik jest członkiem zespołu
     *
     * Używa bezpiecznego wyszukiwania LIKE z escapowanymi wartościami
     * aby znaleźć zadania gdzie nazwa użytkownika występuje w polu 'team'.
     *
     * @return \Illuminate\Database\Eloquent\Builder<Task>
     */
    public function teamTasks()
    {
        // Bezpieczniejsze podejście - używamy LIKE z escapowanymi wartościami
        $escapedName = str_replace(['%', '_'], ['\\%', '\\_'], $this->name);
        return Task::where(function($query) use ($escapedName) {
            $query->where('team', 'LIKE', $escapedName . ',%')      // Na początku
                  ->orWhere('team', 'LIKE', '%, ' . $escapedName . ',%')  // W środku
                  ->orWhere('team', 'LIKE', '%, ' . $escapedName)     // Na końcu
                  ->orWhere('team', '=', $escapedName);               // Jedyny
        })->orderBy('start_date', 'desc');
    }

    /**
     * Pobierz wszystkie zadania dostępne dla użytkownika
     *
     * Dla liderów: zadania gdzie są liderami LUB członkami zespołu
     * Dla pracowników: tylko zadania gdzie są członkami zespołu
     *
     * @return \Illuminate\Database\Eloquent\Builder<Task>
     */
    public function allAccessibleTasks()
    {
        if ($this->isLider()) {
            // Lider sees tasks where they are leader OR part of the team
            $escapedName = str_replace(['%', '_'], ['\\%', '\\_'], $this->name);
            return Task::where(function ($q) use ($escapedName) {
                $q->where('leader_id', $this->id)
                  ->orWhere(function($subQuery) use ($escapedName) {
                      $subQuery->where('team', 'LIKE', $escapedName . ',%')      // Na początku
                               ->orWhere('team', 'LIKE', '%, ' . $escapedName . ',%')  // W środku
                               ->orWhere('team', 'LIKE', '%, ' . $escapedName)     // Na końcu
                               ->orWhere('team', '=', $escapedName);               // Jedyny
                  });
            })->orderBy('start_date', 'desc');
        } else {
            // Pracownik sees only tasks where they are part of the team
            return $this->teamTasks();
        }
    }

    /**
     * Powiązanie z nieobecnościami użytkownika
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<UserAbsence>
     */
    public function absences()
    {
        return $this->hasMany(UserAbsence::class);
    }

    /**
     * Sprawdź czy użytkownik jest nieobecny w podanej dacie
     *
     * @param string|\Carbon\Carbon $date
     * @return bool
     */
    public function isAbsentOn($date)
    {
        if (is_string($date)) {
            $date = \Carbon\Carbon::parse($date);
        }

        return $this->absences()
            ->approved()
            ->where('start_date', '<=', $date->format('Y-m-d'))
            ->where('end_date', '>=', $date->format('Y-m-d'))
            ->exists();
    }

    /**
     * Pobierz nieobecności w danym okresie
     *
     * @param string|\Carbon\Carbon $startDate
     * @param string|\Carbon\Carbon $endDate
     * @return \Illuminate\Database\Eloquent\Collection<UserAbsence>
     */
    public function getAbsencesInPeriod($startDate, $endDate)
    {
        if (is_string($startDate)) {
            $startDate = \Carbon\Carbon::parse($startDate);
        }
        if (is_string($endDate)) {
            $endDate = \Carbon\Carbon::parse($endDate);
        }

        return $this->absences()
            ->approved()
            ->inPeriod($startDate->format('Y-m-d'), $endDate->format('Y-m-d'))
            ->orderBy('start_date')
            ->get();
    }

    /**
     * Sprawdź czy użytkownik ma oczekujące nieobecności
     *
     * @return bool
     */
    public function hasPendingAbsences()
    {
        return $this->absences()->pending()->exists();
    }

    /**
     * Pobierz liczbę dni nieobecności w danym roku
     *
     * @param int|null $year
     * @return int
     */
    public function getAbsenceDaysInYear($year = null)
    {
        $year = $year ?: now()->year;
        $startOfYear = \Carbon\Carbon::create($year, 1, 1)->startOfYear();
        $endOfYear = \Carbon\Carbon::create($year, 12, 31)->endOfYear();

        return $this->absences()
            ->approved()
            ->inPeriod($startOfYear->format('Y-m-d'), $endOfYear->format('Y-m-d'))
            ->get()
            ->sum(function($absence) {
                return $absence->getDaysCount();
            });
    }
}
