<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BuchungProtokoll extends Model
{
    protected $table = 'buchung_protokoll';
    protected $fillable = [
        'buchung_id',
        'user_id',
        'aktion',
        'from_status',
        'to_status',
        'details',
        'daten',
    ];

    protected $casts = [
        'daten' => 'array',
    ];

    public const ACTIONS = [
        'created',
        'updated',
        'status_changed',
        'buchungsbestaetigung_email_versendet',
        'buchungsbestaetigung_pdf_erzeugt',
    ];

    public function buchung()
    {
        return $this->belongsTo(Buchung::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
