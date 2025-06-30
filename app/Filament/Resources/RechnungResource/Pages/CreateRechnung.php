<?php

namespace App\Filament\Resources\RechnungResource\Pages;

use App\Filament\Resources\RechnungResource;
use App\Models\Rechnung;
use App\Services\RechnungService;
use Filament\Resources\Pages\CreateRecord;

class CreateRechnung extends CreateRecord
{
    protected static string $resource = RechnungResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('edit', ['record' => $this->getRecord()]);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Automatische Rechnungsnummer generieren, falls nicht vorhanden
        if (empty($data['rechnungsnummer'])) {
            $data['rechnungsnummer'] = $this->generateRechnungsnummer();
        }

        // Empfänger-Daten aus Aussteller kopieren, falls Aussteller ausgewählt
        if (!empty($data['aussteller_id']) && empty($data['empf_vorname'])) {
            $aussteller = \App\Models\Aussteller::find($data['aussteller_id']);
            if ($aussteller) {
                $data = array_merge($data, [
                    'empf_firma' => $aussteller->firma,
                    'empf_anrede' => $aussteller->anrede,
                    'empf_vorname' => $aussteller->vorname,
                    'empf_name' => $aussteller->name,
                    'empf_strasse' => $aussteller->strasse,
                    'empf_hausnummer' => $aussteller->hausnummer,
                    'empf_plz' => $aussteller->plz,
                    'empf_ort' => $aussteller->ort,
                    'empf_land' => $aussteller->land ?? 'Deutschland',
                    'empf_email' => $aussteller->email,
                ]);
            }
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        $rechnung = $this->getRecord();

        // Wenn Buchung verknüpft ist und keine Positionen vorhanden, erstelle sie aus BuchungLeistungen
        if ($rechnung->buchung_id && $rechnung->positionen()->count() === 0) {
            $this->createPositionenFromBuchung($rechnung);
        }

        // Positionen neu nummerieren (falls manuell erstellt)
        $rechnung->positionen()
            ->orderBy('id')
            ->get()
            ->each(function ($position, $index) {
                $position->update(['position' => $index + 1]);
            });

        // Beträge berechnen
        $rechnung->calculateTotals();
        $rechnung->save();
    }

    private function generateRechnungsnummer(): string
    {
        $year = date('Y');
        $lastNumber = Rechnung::where('rechnungsnummer', 'like', $year . '%')
            ->max('rechnungsnummer');

        if ($lastNumber) {
            $number = intval(substr($lastNumber, -4)) + 1;
        } else {
            $number = 1;
        }

        return $year . sprintf('%04d', $number);
    }

    private function createPositionenFromBuchung(Rechnung $rechnung): void
    {
        $buchung = $rechnung->buchung;
        if (!$buchung) return;

        $position = 1;
        foreach ($buchung->leistungen as $buchungLeistung) {
            $rechnungPosition = \App\Models\RechnungPosition::fromBuchungLeistung($buchungLeistung, $position);
            $rechnung->positionen()->save($rechnungPosition);
            $position++;
        }
    }
}
