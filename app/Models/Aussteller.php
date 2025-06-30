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
        'bilder',
        'files',
    ];

    protected $casts = [
        'soziale_medien' => 'array',
        'bilder' => 'array',
        'files' => 'array',
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
}
