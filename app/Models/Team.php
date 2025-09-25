<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model zespołu
 *
 * Reprezentuje zespół pracowniczy z liderem, członkami i przypisanym pojazdem.
 *
 * @property int $id
 * @property string $name Nazwa zespołu
 * @property string|null $description Opis zespołu
 * @property int $leader_id ID lidera zespołu
 * @property-read \Illuminate\Database\Eloquent\Collection<Vehicle> $vehicles Przypisane pojazdy
 * @property array|null $members Lista ID członków zespołu
 * @property int $created_by ID twórcy zespołu
 * @property bool $active Status aktywności
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 *
 * @property-read User $creator
 * @property-read User $leader
 * @property-read \Illuminate\Database\Eloquent\Collection<Task> $tasks
 * @property-read string $members_names
 */
class Team extends Model
{
    protected $fillable = [
        'name',
        'description',
        'leader_id',
        'members',
        'created_by',
        'active'
    ];

    protected $casts = [
        'members' => 'array',
        'active' => 'boolean'
    ];

    /**
     * Powiązanie z twórcą zespołu
     *
     * @return BelongsTo<User>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Powiązanie z liderem zespołu
     *
     * @return BelongsTo<User>
     */
    public function leader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'leader_id');
    }

    /**
     * Powiązanie z zadaniami zespołu
     *
     * @return HasMany<Task>
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    /**
     * Powiązanie z przypisanymi pojazdami (wiele do wielu)
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany<Vehicle>
     */
    public function vehicles()
    {
        return $this->belongsToMany(Vehicle::class, 'team_vehicles');
    }

    /**
     * Pobierz nazwy członków zespołu jako string
     *
     * @return string
     */
    public function getMembersNamesAttribute(): string
    {
        if (!$this->members) {
            return '';
        }

        $users = User::active()->whereIn('id', $this->members)->pluck('name')->toArray();
        return implode(', ', $users);
    }

    /**
     * Scope dla aktywnych zespołów
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }
}
