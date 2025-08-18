<?php

namespace App\Exports;

use App\Models\Buchung;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Database\Eloquent\Collection;

class BuchungExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected Collection $records;

    public function __construct(Collection $records)
    {
        $this->records = $records;
    }

    public function collection()
    {
        return $this->records;
    }

    public function headings(): array
    {
        return [
            'Status',
            'Markt',
            'Termine',
            'Standort',
            'Standplatz',
            'Aussteller',
            'Firma',
            'E-Mail',
            'Telefon',
            'Mobil',
            'PLZ',
            'Ort',
            'Gebuchte Leistungen',
            'Werbematerial',
            'Bemerkung',
            'Erstellt am',
            'Aktualisiert am'
        ];
    }

    public function map($buchung): array
    {
        // Lade benötigte Relationen
        $buchung->load(['markt', 'standort', 'aussteller', 'leistungen.leistung']);
        
        // Termine formatieren
        $termine = '';
        if ($buchung->termine && is_array($buchung->termine) && count($buchung->termine) > 0) {
            $terminObjekte = \App\Models\Termin::whereIn('id', $buchung->termine)->orderBy('start')->get();
            $terminStrings = [];
            
            foreach ($terminObjekte as $termin) {
                $terminStrings[] = $this->formatDateRange($termin->start, $termin->ende);
            }
            
            $termine = implode(', ', $terminStrings);
        }
        
        // Leistungen formatieren
        $leistungen = $buchung->leistungen->map(function($bl) {
            $name = $bl->leistung ? $bl->leistung->name : 'Unbekannte Leistung';
            $preis = number_format($bl->preis / 100, 2, ',', '.');
            $menge = $bl->menge;
            return "{$name} ({$menge}x {$preis} €)";
        })->implode(', ');
        
        // Werbematerial formatieren
        $werbematerial = '';
        if ($buchung->werbematerial && is_array($buchung->werbematerial)) {
            $materialien = [];
            foreach ($buchung->werbematerial as $material) {
                $typ = $material['typ'] ?? '';
                $anzahl = $material['anzahl'] ?? 0;
                $format = [];
                if (isset($material['physisch']) && $material['physisch']) {
                    $format[] = 'physisch';
                }
                if (isset($material['digital']) && $material['digital']) {
                    $format[] = 'digital';
                }
                $formatStr = count($format) > 0 ? ' (' . implode(', ', $format) . ')' : '';
                $materialien[] = "{$typ}: {$anzahl}{$formatStr}";
            }
            $werbematerial = implode(', ', $materialien);
        }
        
        // Status Label
        $statusLabels = [
            'anfrage' => 'Anfrage',
            'bearbeitung' => 'Bearbeitung',
            'bestätigt' => 'Bestätigt',
            'erledigt' => 'Erledigt',
            'abgelehnt' => 'Abgelehnt',
        ];
        
        return [
            $statusLabels[$buchung->status] ?? $buchung->status,
            $buchung->markt ? $buchung->markt->name : '',
            $termine,
            $buchung->standort ? $buchung->standort->name : '',
            $buchung->standplatz ?? '',
            $this->formatAusstellerName($buchung->aussteller),
            $buchung->aussteller ? $buchung->aussteller->firma : '',
            $buchung->aussteller ? $buchung->aussteller->email : '',
            $buchung->aussteller ? $buchung->aussteller->telefon : '',
            $buchung->aussteller ? $buchung->aussteller->mobil : '',
            $buchung->aussteller ? $buchung->aussteller->plz : '',
            $buchung->aussteller ? $buchung->aussteller->ort : '',
            $leistungen,
            $werbematerial,
            $buchung->bemerkung ?? '',
            $buchung->created_at?->format('d.m.Y H:i'),
            $buchung->updated_at?->format('d.m.Y H:i'),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Header-Zeile fett machen
            1 => ['font' => ['bold' => true]],

            // Auto-Width für alle Spalten
            'A:Q' => ['alignment' => ['wrap_text' => true]],
        ];
    }
    
    protected function formatDateRange($start, $ende): string
    {
        $startDate = \Carbon\Carbon::parse($start);
        $endDate = \Carbon\Carbon::parse($ende);

        if ($startDate->format('m') === $endDate->format('m')) {
            // Gleicher Monat
            return $startDate->format('d.') . '-' . $endDate->format('d.m.Y');
        } elseif ($startDate->format('Y') === $endDate->format('Y')) {
            // Gleiches Jahr, aber unterschiedlicher Monat
            return $startDate->format('d.m.') . '-' . $endDate->format('d.m.Y');
        } else {
            // Unterschiedliche Jahre
            return $startDate->format('d.m.Y') . ' - ' . $endDate->format('d.m.Y');
        }
    }
    
    protected function formatAusstellerName($aussteller): string
    {
        if (!$aussteller) {
            return '';
        }
        
        $parts = [];

        if ($aussteller->firma) {
            $parts[] = $aussteller->firma;
        }

        if ($aussteller->vorname && $aussteller->name) {
            $parts[] = "{$aussteller->name}, {$aussteller->vorname}";
        } elseif ($aussteller->name) {
            $parts[] = $aussteller->name;
        }

        return implode(' | ', $parts);
    }
}