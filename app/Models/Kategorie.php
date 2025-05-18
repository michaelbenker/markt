<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kategorie extends Model
{
    public function subkategorien()
    {
        return $this->hasMany(Subkategorie::class);
    }
}
