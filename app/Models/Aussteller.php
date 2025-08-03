<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Aussteller extends Model
{
    use HasFactory;

    protected $table = 'aussteller';
    protected $fillable = [
        'firma',
        'anrede',
        'vorname',
        'name',
        'strasse',
        'hausnummer',
        'plz',
        'ort',
        'land',
        'telefon',
        'mobil',
        'homepage',
        'email',
        'briefanrede',
        'bemerkung',
        'soziale_medien',
        'stand',
    ];

    protected $casts = [
        'soziale_medien' => 'array',
        'stand' => 'array',
    ];

    public function kategorien()
    {
        return $this->belongsToMany(Kategorie::class);
    }

    public function subkategorien()
    {
        return $this->belongsToMany(Subkategorie::class);
    }

    public function buchungen()
    {
        return $this->hasMany(Buchung::class);
    }

    public function rechnungen()
    {
        return $this->hasMany(Rechnung::class);
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

    /**
     * Gibt den vollständigen Namen des Ausstellers zurück
     */
    public function getFullName(): string
    {
        return trim($this->vorname . ' ' . $this->name);
    }
}
