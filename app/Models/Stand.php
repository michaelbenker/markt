<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stand extends Model
{
    use HasFactory;

    protected $table = 'stand';
    protected $fillable = [
        'nummer',
        'flaeche',
        'laenge',
        'bemerkung',
        'longitude',
        'latitude',
    ];
}
