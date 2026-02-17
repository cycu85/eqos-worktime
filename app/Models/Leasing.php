<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Leasing extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_id',
        'leasing_cost_type_id',
        'lessor',
        'contract_number',
        'date_from',
        'date_to',
        'amount',
        'payment_date',
        'description',
    ];

    protected $casts = [
        'amount' => 'float',
        'date_from' => 'date',
        'date_to' => 'date',
        'payment_date' => 'date',
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
