<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

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
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isKierownik(): bool
    {
        return $this->role === 'kierownik';
    }

    public function isLider(): bool
    {
        return $this->role === 'lider';
    }

    public function isPracownik(): bool
    {
        return $this->role === 'pracownik';
    }

    public function tasks()
    {
        return $this->hasMany(Task::class, 'leader_id');
    }

    /**
     * Get tasks where this user is part of the team
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
     * Get all tasks accessible to this user (as leader OR team member)
     * For liders: shows both led tasks and team tasks
     * For pracowniks: shows only team tasks
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
}
