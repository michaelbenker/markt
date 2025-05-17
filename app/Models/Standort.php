<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Standort extends Model
{
    protected $fillable = [
        'name',
        'beschreibung',
        'flaeche',
    ];

    public function markt()
    {
        return $this->belongsTo(Markt::class);
    }
}
