<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Termin extends Model
{
    protected $table = 'termin';

    protected $fillable = [
        'markt_id',
        'start',
        'ende',
        'bemerkung',
    ];

    public function markt()
    {
        return $this->belongsTo(Markt::class);
    }
}
