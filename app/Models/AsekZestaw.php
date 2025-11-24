<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AsekZestaw extends Model
{
    /**
     * Połączenie z zewnętrzną bazą danych (tylko odczyt)
     */
    protected $connection = 'mysql_external';

    /**
     * Nazwa tabeli
     */
    protected $table = 'asek_zestawy';

    /**
     * Wyłączamy timestamps - tabela używa własnych pól date_mod
     */
    public $timestamps = false;

    /**
     * Pola do castowania
     */
    protected $casts = [
        'date_mod' => 'datetime',
    ];

    /**
     * Elementy zestawu (tickety)
     */
    public function tickets(): HasMany
    {
        return $this->hasMany(AsekTicket::class, 'zestaw_id', 'id');
    }

    /**
     * Pobierz liczbę elementów w zestawie
     */
    public function getTicketsCountAttribute(): int
    {
        return $this->tickets()->count();
    }
}
