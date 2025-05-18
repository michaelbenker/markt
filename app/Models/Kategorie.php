<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kategorie extends Model
{
    protected $fillable = [
        'name',
        'bemerkung',
    ];

    public function subkategorien()
    {
        return $this->hasMany(Subkategorie::class);
    }
}
