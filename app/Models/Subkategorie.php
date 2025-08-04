<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subkategorie extends Model
{
    protected $table = 'subkategorie';
    protected $fillable = [
        'name',
        'bemerkung',
        'kategorie_id',
    ];

    public function kategorie()
    {
        return $this->belongsTo(Kategorie::class);
    }

    public function aussteller()
    {
        return $this->belongsToMany(Aussteller::class);
    }
}
