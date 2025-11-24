<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AsekTicket extends Model
{
    /**
     * Połączenie z zewnętrzną bazą danych (tylko odczyt)
     */
    protected $connection = 'mysql_external';

    /**
     * Nazwa tabeli
     */
    protected $table = 'asek_ticket';

    /**
     * Wyłączamy timestamps - tabela używa własnych pól date_mod
     */
    public $timestamps = false;

    /**
     * Pola do castowania
     */
    protected $casts = [
        'date_calib' => 'date',
        'date_next_calib' => 'date',
        'date_buy' => 'date',
        'warranty_end_date' => 'date',
        'date_mod' => 'datetime',
    ];

    /**
     * Zestaw do którego należy element
     */
    public function zestaw(): BelongsTo
    {
        return $this->belongsTo(AsekZestaw::class, 'zestaw_id', 'id');
    }

    /**
     * Sprawdź czy wymaga kalibracji (mniej niż 30 dni)
     */
    public function getRequiresCalibrationAttribute(): bool
    {
        if (!$this->date_next_calib) {
            return false;
        }
        return $this->date_next_calib->isBefore(now()->addDays(30));
    }

    /**
     * Sprawdź czy kalibracja jest przeterminowana
     */
    public function getCalibrationOverdueAttribute(): bool
    {
        if (!$this->date_next_calib) {
            return false;
        }
        return $this->date_next_calib->isPast();
    }
}
