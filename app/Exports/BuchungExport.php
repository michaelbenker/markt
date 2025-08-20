<?php

namespace App\Exports;

use App\Models\Buchung;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Illuminate\Database\Eloquent\Collection;

class BuchungExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithEvents
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
            // Header-Zeile mit Formatierung
            1 => [
                'font' => [
                    'bold' => true,
                    'size' => 12,
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E7E7E7'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
        ];
    }
    
    /**
     * Registriere Events für erweiterte Formatierung
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                
                // Freeze erste Zeile (Header)
                $sheet->freezePane('A2');
                
                // Setze Zeilenhöhe für Header
                $sheet->getRowDimension(1)->setRowHeight(25);
                
                // Füge Rahmen hinzu
                $lastColumn = $sheet->getHighestColumn();
                $lastRow = $sheet->getHighestRow();
                $sheet->getStyle('A1:' . $lastColumn . $lastRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => 'CCCCCC'],
                        ],
                    ],
                ]);
                
                // Setze Filter für alle Spalten
                $sheet->setAutoFilter('A1:' . $lastColumn . $lastRow);
                
                // Formatiere Status-Spalte mit Farben
                for ($row = 2; $row <= $lastRow; $row++) {
                    $status = $sheet->getCell('A' . $row)->getValue();
                    $color = match($status) {
                        'Bestätigt' => '00B050', // Grün
                        'Anfrage' => 'FFC000', // Orange
                        'Bearbeitung' => '0070C0', // Blau
                        'Abgelehnt' => 'FF0000', // Rot
                        default => '000000', // Schwarz
                    };
                    $sheet->getStyle('A' . $row)->getFont()->getColor()->setRGB($color);
                }
                
                // Formatiere E-Mail-Spalte als Hyperlink (Spalte H = Email)
                for ($row = 2; $row <= $lastRow; $row++) {
                    $email = $sheet->getCell('H' . $row)->getValue();
                    if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        $sheet->getCell('H' . $row)->getHyperlink()->setUrl('mailto:' . $email);
                        $sheet->getStyle('H' . $row)->getFont()->setUnderline(true)->getColor()->setRGB('0000FF');
                    }
                }
                
                // Setze Spaltenbreiten für bestimmte Spalten manuell (optional)
                $sheet->getColumnDimension('M')->setWidth(50); // Gebuchte Leistungen
                $sheet->getColumnDimension('N')->setWidth(40); // Werbematerial
                $sheet->getColumnDimension('O')->setWidth(50); // Bemerkung
            },
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