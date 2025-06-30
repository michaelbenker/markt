<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Rechnung extends Model
{
    use HasFactory;

    protected $table = 'rechnung';

    protected $fillable = [
        'rechnungsnummer',
        'status',
        'buchung_id',
        'aussteller_id',
        'rechnungsdatum',
        'lieferdatum',
        'faelligkeitsdatum',
        'betreff',
        'anschreiben',
        'schlussschreiben',
        'zahlungsziel',
        'gesamtrabatt_prozent',
        'gesamtrabatt_betrag',
        'nettobetrag',
        'steuerbetrag',
        'bruttobetrag',
        'empf_firma',
        'empf_anrede',
        'empf_vorname',
        'empf_name',
        'empf_strasse',
        'empf_hausnummer',
        'empf_plz',
        'empf_ort',
        'empf_land',
        'empf_email',
        'versendet_am',
        'bezahlt_am',
        'bezahlter_betrag',
        'zugferd_enabled',
        'zugferd_xml',
    ];

    protected $casts = [
        'rechnungsdatum' => 'date',
        'lieferdatum' => 'date',
        'faelligkeitsdatum' => 'date',
        'versendet_am' => 'datetime',
        'bezahlt_am' => 'datetime',
        'gesamtrabatt_prozent' => 'decimal:2',
        'gesamtrabatt_betrag' => 'integer', // Cent
        'nettobetrag' => 'integer', // Cent
        'steuerbetrag' => 'integer', // Cent
        'bruttobetrag' => 'integer', // Cent
        'bezahlter_betrag' => 'integer', // Cent
        'zugferd_enabled' => 'boolean',
    ];

    // Automatische Rechnungsnummer beim Erstellen
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->rechnungsnummer)) {
                $model->rechnungsnummer = self::generateRechnungsnummer();
            }
        });
    }

    // Relationships
    public function buchung()
    {
        return $this->belongsTo(Buchung::class);
    }

    public function aussteller()
    {
        return $this->belongsTo(Aussteller::class);
    }

    public function positionen()
    {
        return $this->hasMany(RechnungPosition::class)->orderBy('position');
    }

    // Helper Methods
    public function isManuelleRechnung(): bool
    {
        return $this->buchung_id === null;
    }

    // Helper Methods
    public function isEditable(): bool
    {
        return $this->status === 'draft';
    }

    public function isOverdue(): bool
    {
        return $this->status === 'sent' &&
            $this->faelligkeitsdatum < now()->toDateString() &&
            $this->bezahlter_betrag < $this->bruttobetrag;
    }

    public function getOffenerBetragAttribute(): float
    {
        return $this->bruttobetrag - $this->bezahlter_betrag;
    }

    // Rechnungsnummer generieren
    private static function generateRechnungsnummer(): string
    {
        $year = date('Y');
        $lastNumber = self::where('rechnungsnummer', 'like', $year . '%')
            ->max('rechnungsnummer');

        if ($lastNumber) {
            $number = intval(substr($lastNumber, -4)) + 1;
        } else {
            $number = 1;
        }

        return $year . sprintf('%04d', $number);
    }

    // Empfänger-Daten aus Aussteller kopieren
    public function copyAusstellerData(Aussteller $aussteller): void
    {
        $this->fill([
            'empf_firma' => $aussteller->firma,
            'empf_anrede' => $aussteller->anrede,
            'empf_vorname' => $aussteller->vorname,
            'empf_name' => $aussteller->name,
            'empf_strasse' => $aussteller->strasse,
            'empf_hausnummer' => $aussteller->hausnummer,
            'empf_plz' => $aussteller->plz,
            'empf_ort' => $aussteller->ort,
            'empf_land' => $aussteller->land,
            'empf_email' => $aussteller->email,
        ]);
    }

    // Beträge neu berechnen
    public function calculateTotals(): void
    {
        $nettoGesamt = $this->positionen->sum('nettobetrag');
        $steuerGesamt = $this->positionen->sum('steuerbetrag');

        // Rabatt anwenden
        if ($this->gesamtrabatt_prozent > 0) {
            $this->gesamtrabatt_betrag = $nettoGesamt * ($this->gesamtrabatt_prozent / 100);
            $nettoGesamt -= $this->gesamtrabatt_betrag;
            $steuerGesamt = $nettoGesamt * 0.19; // Neu berechnen nach Rabatt
        }

        $this->nettobetrag = $nettoGesamt;
        $this->steuerbetrag = $steuerGesamt;
        $this->bruttobetrag = $nettoGesamt + $steuerGesamt;
    }

    // PDF generieren
    public function generatePdf(): string
    {
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.rechnung', [
            'rechnung' => $this->load('positionen', 'aussteller')
        ]);

        $pdf->setPaper('A4', 'portrait');

        return $pdf->output();
    }

    // Accessors für Filament (Cent -> Euro)
    public function getGesamtrabattBetragEuroAttribute(): float
    {
        return $this->gesamtrabatt_betrag / 100;
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

    public function getBezahlterBetragEuroAttribute(): float
    {
        return $this->bezahlter_betrag / 100;
    }

    // Mutators für Filament (Euro -> Cent)
    public function setGesamtrabattBetragEuroAttribute($value): void
    {
        $this->attributes['gesamtrabatt_betrag'] = round($value * 100);
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

    public function setBezahlterBetragEuroAttribute($value): void
    {
        $this->attributes['bezahlter_betrag'] = round($value * 100);
    }
}
