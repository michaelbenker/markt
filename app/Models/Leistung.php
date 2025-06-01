<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Leistung extends Model
{
    use HasFactory;

    protected $table = 'leistung';

    protected $fillable = [
        'name',
        'kategorie',
        'bemerkung',
        'einheit',
        'preis',
    ];

    public function buchungen()
    {
        return $this->belongsToMany(Buchungleistung::class)
            ->withPivot('preis', 'menge')
            ->withTimestamps();
    }
}
