<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Anfrage extends Model
{
    use HasFactory;

    protected $table = 'anfrage';

    protected $fillable = [
        'termin_id',
        'firma',
        'anrede',
        'vorname',
        'nachname',
        'strasse',
        'hausnummer',
        'plz',
        'ort',
        'land',
        'telefon',
        'email',
        'stand',
        'warenangebot',
        'herkunft',
        'bereits_ausgestellt',
        'importiert',
        'bemerkung',
    ];

    protected $casts = [
        'stand' => 'array',
        'warenangebot' => 'array',
        'herkunft' => 'array',
        'bereits_ausgestellt' => 'boolean',
        'importiert' => 'boolean',
    ];

    public function termin()
    {
        return $this->belongsTo(Termin::class);
    }

    // Shortcut für Markt über Termin
    public function markt()
    {
        return $this->hasOneThrough(Markt::class, Termin::class, 'id', 'id', 'termin_id', 'markt_id');
    }
}
