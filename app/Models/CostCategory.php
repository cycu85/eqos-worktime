<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CostCategory extends Model
{
    protected $fillable = ['name', 'description', 'active'];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function costs(): HasMany
    {
        return $this->hasMany(Cost::class);
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }
}
