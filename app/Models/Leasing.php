<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Leasing extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'vehicle_id',
        'leasing_cost_type_id',
        'amount',
        'cost_date',
        'description',
    ];

    protected $casts = [
        'amount' => 'float',
        'cost_date' => 'date',
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function leasingCostType()
    {
        return $this->belongsTo(LeasingCostType::class);
    }
}
