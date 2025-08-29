<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function leader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'leader_id');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function getMembersNamesAttribute(): string
    {
        if (!$this->members) {
            return '';
        }

        $users = User::whereIn('id', $this->members)->pluck('name')->toArray();
        return implode(', ', $users);
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }
}
