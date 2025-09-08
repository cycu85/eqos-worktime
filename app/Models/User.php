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
        return Task::whereRaw("FIND_IN_SET(?, REPLACE(team, ', ', ','))", [$this->name])->orderBy('start_date', 'desc');
    }
}
