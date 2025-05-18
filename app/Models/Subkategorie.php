<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subkategorie extends Model
{
    public function kategorie()
    {
        return $this->belongsTo(Kategorie::class);
    }

    public function aussteller()
    {
        return $this->belongsToMany(Aussteller::class);
    }
}
