<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Buchung extends Model
{
    protected $table = 'buchung';
    protected $fillable = [
        'status',
        'termin_id',
        'standort_id',
        'standplatz',
        'aussteller_id',
        'stand',
        'warenangebot',
        'herkunft',
    ];

    protected $casts = [
        'stand' => 'array',
        'warenangebot' => 'array',
        'herkunft' => 'array',
    ];

    public function termin()
    {
        return $this->belongsTo(Termin::class);
    }

    public function standort()
    {
        return $this->belongsTo(Standort::class);
    }

    public function aussteller()
    {
        return $this->belongsTo(Aussteller::class);
    }

    public function leistungen()
    {
        return $this->hasMany(Buchungleistung::class);
    }
}
