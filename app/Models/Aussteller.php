<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Parallax\FilamentComments\Models\Traits\HasFilamentComments;

class Aussteller extends Model
{
    use HasFactory;
    use HasFilamentComments;
    use SoftDeletes;

    protected $table = 'aussteller';
    protected $fillable = [
        'firma',
        'anrede',
        'vorname',
        'name',
        'strasse',
        'hausnummer',
        'plz',
        'ort',
        'land',
        'telefon',
        'mobil',
        'homepage',
        'email',
        'briefanrede',
        'bemerkung',
        'vorfuehrung_am_stand',
        'steuer_id',
        'handelsregisternummer',
        'herkunft',
        'rating',
        'rating_bemerkung',
        'soziale_medien',
        'stand',
    ];

    protected $casts = [
        'stand' => 'array',
        'rating' => 'integer',
        'herkunft' => 'array',
        'vorfuehrung_am_stand' => 'boolean',
    ];

    protected $attributes = [
        'rating' => 0,
    ];

    public function kategorien()
    {
        return $this->belongsToMany(Kategorie::class);
    }

    public function subkategorien()
    {
        return $this->belongsToMany(Subkategorie::class);
    }

    public function buchungen()
    {
        return $this->hasMany(Buchung::class);
    }

    public function rechnungen()
    {
        return $this->hasMany(Rechnung::class);
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

    /**
     * Tags des Ausstellers
     */
    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'aussteller_tag')
            ->withPivot('notiz')
            ->withTimestamps();
    }

    /**
     * Gibt den vollständigen Namen des Ausstellers zurück
     */
    public function getFullName(): string
    {
        return trim($this->vorname . ' ' . $this->name);
    }

    /**
     * Konvertiert soziale Medien für Filament Repeater
     */
    public function getSozialeMedienAttribute($value)
    {
        if (!$value) {
            return [];
        }

        $data = is_string($value) ? json_decode($value, true) : $value;

        if (!is_array($data)) {
            return [];
        }

        $result = [];
        $platformMap = [
            'facebook' => 'facebook',
            'instagram' => 'instagram',
            'twitter' => 'x',
            'x' => 'x',
            'linkedin' => 'linkedin',
            'youtube' => 'youtube',
            'tiktok' => 'tiktok',
            'pinterest' => 'pinterest',
            'xing' => 'xing',
            'website' => 'other',
        ];

        foreach ($data as $key => $url) {
            if (!empty($url)) {
                $result[] = [
                    'plattform' => $platformMap[$key] ?? 'other',
                    'url' => $url,
                ];
            }
        }

        return $result;
    }

    /**
     * Konvertiert Filament Repeater zurück zu flachem Format
     */
    public function setSozialeMedienAttribute($value)
    {
        if (!is_array($value)) {
            $this->attributes['soziale_medien'] = null;
            return;
        }

        // Prüfen ob es bereits das flache Format ist (von Anfrage)
        if (isset($value['facebook']) || isset($value['instagram']) || isset($value['twitter']) || isset($value['website'])) {
            // Bereits im richtigen Format, einfach speichern
            $this->attributes['soziale_medien'] = json_encode($value);
            return;
        }

        // Filament Repeater Format konvertieren
        $result = [];
        $reverseMap = [
            'facebook' => 'facebook',
            'instagram' => 'instagram',
            'x' => 'twitter',
            'linkedin' => 'linkedin',
            'youtube' => 'youtube',
            'tiktok' => 'tiktok',
            'pinterest' => 'pinterest',
            'xing' => 'xing',
            'other' => 'website',
        ];

        foreach ($value as $item) {
            if (!empty($item['plattform']) && !empty($item['url'])) {
                $key = $reverseMap[$item['plattform']] ?? $item['plattform'];
                $result[$key] = $item['url'];
            }
        }

        $this->attributes['soziale_medien'] = json_encode($result);
    }
}
