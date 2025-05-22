<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Preis extends Model
{
    use HasFactory;

    protected $table = 'preis';

    protected $fillable = [
        'name',
        'kategorie',
        'bemerkung',
        'einheit',
        'preis',
    ];
}
