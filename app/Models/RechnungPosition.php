<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RechnungPosition extends Model
{
    use HasFactory;

    protected $table = 'rechnung_position';

    protected $fillable = [
        'rechnung_id',
        'buchung_leistung_id',
        'position',
        'bezeichnung',
        'beschreibung',
        'menge',
        'einheit',
        'einzelpreis',
        'rabatt_prozent',
        'nettobetrag',
        'steuersatz',
        'steuerbetrag',
        'bruttobetrag',
    ];

    protected $casts = [
        'menge' => 'decimal:2',
        'einzelpreis' => 'integer', // Cent
        'rabatt_prozent' => 'decimal:2',
        'nettobetrag' => 'integer', // Cent
        'steuersatz' => 'decimal:2',
        'steuerbetrag' => 'integer', // Cent
        'bruttobetrag' => 'integer', // Cent
    ];

    // Relationships
    public function rechnung()
    {
        return $this->belongsTo(Rechnung::class);
    }

    public function buchungLeistung()
    {
        return $this->belongsTo(BuchungLeistung::class);
    }

    // Beträge automatisch berechnen
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            $model->calculateAmounts();
        });
    }

    public function calculateAmounts(): void
    {
        // Nettobetrag berechnen
        $netto = $this->menge * $this->einzelpreis;

        // Rabatt abziehen
        if ($this->rabatt_prozent > 0) {
            $netto = $netto * (1 - ($this->rabatt_prozent / 100));
        }

        // Steuer berechnen
        $steuer = $netto * ($this->steuersatz / 100);

        $this->nettobetrag = $netto;
        $this->steuerbetrag = $steuer;
        $this->bruttobetrag = $netto + $steuer;
    }

    // Position aus BuchungLeistung erstellen
    public static function fromBuchungLeistung(BuchungLeistung $buchungLeistung, int $position = 1): self
    {
        $rechnungPosition = new self();
        $rechnungPosition->buchung_leistung_id = $buchungLeistung->id;
        $rechnungPosition->position = $position;
        $rechnungPosition->bezeichnung = $buchungLeistung->leistung->name ?? 'Leistung';
        $rechnungPosition->beschreibung = $buchungLeistung->leistung->beschreibung ?? null;
        $rechnungPosition->menge = $buchungLeistung->menge;
        $rechnungPosition->einzelpreis = $buchungLeistung->preis;
        $rechnungPosition->steuersatz = 19.00; // Standard MwSt

        return $rechnungPosition;
    }

    // Accessors für Filament (Cent -> Euro)
    public function getEinzelpreisEuroAttribute(): float
    {
        return $this->einzelpreis / 100;
    }

    public function getNettobetragEuroAttribute(): float
    {
        return $this->nettobetrag / 100;
    }

    public function getSteuerbetragEuroAttribute(): float
    {
        return $this->steuerbetrag / 100;
    }

    public function getBruttobetragEuroAttribute(): float
    {
        return $this->bruttobetrag / 100;
    }

    // Mutators für Filament (Euro -> Cent)
    public function setEinzelpreisEuroAttribute($value): void
    {
        $this->attributes['einzelpreis'] = round($value * 100);
    }

    public function setNettobetragEuroAttribute($value): void
    {
        $this->attributes['nettobetrag'] = round($value * 100);
    }

    public function setSteuerbetragEuroAttribute($value): void
    {
        $this->attributes['steuerbetrag'] = round($value * 100);
    }

    public function setBruttobetragEuroAttribute($value): void
    {
        $this->attributes['bruttobetrag'] = round($value * 100);
    }
}
