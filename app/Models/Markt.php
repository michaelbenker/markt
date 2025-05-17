<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Markt extends Model
{
    protected $fillable = [
        'name',
        'bemerkung',
        'url',
    ];

    public function standorte()
    {
        return $this->hasMany(Standort::class);
    }
}
