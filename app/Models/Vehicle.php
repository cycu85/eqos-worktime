<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Model pojazdu/sprzętu
 *
 * Reprezentuje pojazd lub sprzęt używany w zadaniach.
 *
 * @property int $id
 * @property string $registration Numer rejestracyjny
 * @property string $name Nazwa pojazdu
 * @property string|null $description Opis
 * @property bool $is_active Status aktywności
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 *
 * @property-read \Illuminate\Database\Eloquent\Collection<Task> $tasks
 * @property-read \Illuminate\Database\Eloquent\Collection<Team> $teams
 */
class Vehicle extends Model
{
    use HasFactory;

    protected $fillable = [
        'registration',
        'name',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Powiązanie z zadaniami (wiele do wielu)
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany<Task>
     */
    public function tasks()
    {
        return $this->belongsToMany(Task::class, 'task_vehicles');
    }

    /**
     * Powiązanie z zespołami
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<Team>
     */
    public function teams()
    {
        return $this->hasMany(Team::class);
    }

    /**
     * Scope dla aktywnych pojazdów
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
