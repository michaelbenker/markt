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

    /**
     * Polymorphic Relation zu Medien
     */
    public function medien()
    {
        return $this->morphMany(Medien::class, 'mediable')->orderBy('sort_order');
    }

    /**
     * Hilfsmethoden für spezifische Medien-Kategorien
     */
    public function detailfotos()
    {
        return $this->medien()->category('angebot');
    }

    public function standfotos()
    {
        return $this->medien()->category('stand');
    }

    public function werkstattfotos()
    {
        return $this->medien()->category('werkstatt');
    }

    public function vitaDokumente()
    {
        return $this->medien()->category('vita');
    }
}
