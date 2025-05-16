<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Aussteller extends Model
{
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
}
