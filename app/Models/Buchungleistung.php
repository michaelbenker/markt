<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BuchungLeistung extends Model
{
    protected $table = 'buchung_leistung';
    protected $fillable = [
        'leistung_id',
        'preis',
        'menge',
    ];

    public function buchung()
    {
        return $this->belongsTo(Buchung::class);
    }

    public function leistung()
    {
        return $this->belongsTo(Leistung::class);
    }
}
