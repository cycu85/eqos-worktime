<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cost extends Model
{
    protected $fillable = ['name', 'amount', 'cost_date', 'cost_category_id', 'description'];

    protected $casts = [
        'amount' => 'float',
        'cost_date' => 'date',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(CostCategory::class, 'cost_category_id');
    }
}
