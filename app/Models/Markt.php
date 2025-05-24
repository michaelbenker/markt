<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Markt extends Model
{
    protected $table = 'markt';
    protected $fillable = [
        'slug',
        'name',
        'bemerkung',
        'url',
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
