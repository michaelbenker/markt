<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Termin extends Model
{
    protected $table = 'termin';

    protected $fillable = [
        'markt_id',
        'start',
        'ende',
        'bemerkung',
        'anmeldeschluss',
    ];

    protected $casts = [
        'start' => 'datetime',
        'ende' => 'datetime',
        'anmeldeschluss' => 'date',
    ];

    public function markt()
    {
        return $this->belongsTo(Markt::class);
    }
}
