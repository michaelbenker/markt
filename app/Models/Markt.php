<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Markt extends Model
{
    use HasFactory;

    protected $table = 'markt';
    protected $fillable = [
        'name',
        'slug',
        'beschreibung',
        'start',
        'ende',
        'ort',
        'strasse',
        'hausnummer',
        'plz',
        'land',
        'status',
    ];

    protected $casts = [
        'start' => 'datetime',
        'ende' => 'datetime',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function standorte()
    {
        return $this->hasMany(Standort::class);
    }

    public function termine()
    {
        return $this->hasMany(Termin::class);
    }
}
