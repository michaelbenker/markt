<?php

namespace App\Services;

use App\Models\Rechnung;
use App\Models\RechnungPosition;
use App\Models\Buchung;
use App\Models\Aussteller;

class RechnungService
{
    /**
     * Erstelle Rechnung aus Buchung
     */
    public static function createFromBuchung(Buchung $buchung, array $overrides = []): Rechnung
    {
        $rechnung = new Rechnung(array_merge([
            'buchung_id' => $buchung->id,
            'aussteller_id' => $buchung->aussteller_id,
            'rechnungsdatum' => now()->toDateString(),
            'lieferdatum' => now()->toDateString(),
            'faelligkeitsdatum' => now()->addDays(14)->toDateString(),
            'betreff' => 'Rechnung für ' . ($buchung->markt->name ?? 'Markt'),
            'zahlungsziel' => '14 Tage netto',
        ], $overrides));

        // Empfänger-Daten kopieren
        $rechnung->copyAusstellerData($buchung->aussteller);
        $rechnung->save();

        // Positionen aus BuchungLeistungen erstellen
        $position = 1;
        foreach ($buchung->leistungen as $buchungLeistung) {
            $rechnungPosition = RechnungPosition::fromBuchungLeistung($buchungLeistung, $position);
            $rechnung->positionen()->save($rechnungPosition);
            $position++;
        }

        // Beträge berechnen
        $rechnung->calculateTotals();
        $rechnung->save();

        return $rechnung;
    }

    /**
     * Erstelle manuelle Rechnung
     */
    public static function createManuelleRechnung(Aussteller $aussteller, array $data = []): Rechnung
    {
        $rechnung = new Rechnung(array_merge([
            'buchung_id' => null, // Keine Buchung verknüpft
            'aussteller_id' => $aussteller->id,
            'rechnungsdatum' => now()->toDateString(),
            'faelligkeitsdatum' => now()->addDays(14)->toDateString(),
            'betreff' => 'Rechnung',
            'zahlungsziel' => '14 Tage netto',
        ], $data));

        // Empfänger-Daten kopieren
        $rechnung->copyAusstellerData($aussteller);
        $rechnung->save();

        return $rechnung;
    }

    /**
     * Füge Position zu Rechnung hinzu
     */
    public static function addPosition(Rechnung $rechnung, array $positionData): RechnungPosition
    {
        if (!$rechnung->isEditable()) {
            throw new \Exception('Rechnung ist nicht mehr editierbar');
        }

        $maxPosition = $rechnung->positionen()->max('position') ?? 0;

        $position = new RechnungPosition(array_merge([
            'position' => $maxPosition + 1,
            'steuersatz' => 19.00,
        ], $positionData));

        $rechnung->positionen()->save($position);

        // Beträge neu berechnen
        $rechnung->calculateTotals();
        $rechnung->save();

        return $position;
    }
}
