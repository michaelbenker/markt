<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Parallax\FilamentComments\Models\Traits\HasFilamentComments;

class Anfrage extends Model
{
    use HasFactory;
    use HasFilamentComments;

    protected $table = 'anfrage';

    protected $fillable = [
        'markt_id',
        'termine',
        'termin_id',
        'firma',
        'anrede',
        'vorname',
        'nachname',
        'strasse',
        'hausnummer',
        'plz',
        'ort',
        'land',
        'telefon',
        'email',
        'steuer_id',
        'handelsregisternummer',
        'stand',
        'warenangebot',
        'herkunft',
        'bereits_ausgestellt',
        'vorfuehrung_am_stand',
        'status',
        'bemerkung',
        'soziale_medien',
        'wuensche_zusatzleistungen',
        'werbematerial',
        'wunsch_standort_id',
    ];

    protected $casts = [
        'termine' => 'array',
        'stand' => 'array',
        'warenangebot' => 'array',
        'herkunft' => 'array',
        'vorfuehrung_am_stand' => 'boolean',
        'soziale_medien' => 'array',
        'wuensche_zusatzleistungen' => 'array',
        'werbematerial' => 'array',
    ];

    public function termin()
    {
        return $this->belongsTo(Termin::class);
    }

    // Direkte Relation zum Markt
    public function markt()
    {
        return $this->belongsTo(Markt::class);
    }

    // Accessor für mehrere Termine
    public function getTermineAttribute($value)
    {
        // Wenn termine JSON vorhanden ist, diese verwenden
        if ($value) {
            $terminIds = is_array($value) ? $value : json_decode($value, true);
            return Termin::whereIn('id', $terminIds)->get();
        }
        // Fallback auf termin_id für Rückwärtskompatibilität
        if ($this->termin_id) {
            return Termin::where('id', $this->termin_id)->get();
        }
        return collect();
    }

    /**
     * Polymorphic Relation zu Medien
     */
    public function medien()
    {
        return $this->morphMany(Medien::class, 'mediable')->orderBy('sort_order');
    }

    /**
     * Hilfsmethoden für spezifische Medien-Kategorien
     */
    public function detailfotos()
    {
        return $this->medien()->category('angebot');
    }

    public function standfotos()
    {
        return $this->medien()->category('stand');
    }

    public function werkstattfotos()
    {
        return $this->medien()->category('werkstatt');
    }

    public function vitaDokumente()
    {
        return $this->medien()->category('vita');
    }

    public function wunschStandort()
    {
        return $this->belongsTo(Standort::class, 'wunsch_standort_id');
    }

    /**
     * Beziehung zu den gewünschten Leistungen
     */
    public function gewuenschteLeistungen()
    {
        if (!$this->wuensche_zusatzleistungen) {
            return collect();
        }

        return \App\Models\Leistung::whereIn('id', $this->wuensche_zusatzleistungen)->get();
    }
}
