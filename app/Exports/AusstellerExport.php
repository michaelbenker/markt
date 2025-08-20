<?php

namespace App\Exports;

use App\Models\Aussteller;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Illuminate\Database\Eloquent\Collection;

class AusstellerExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithEvents
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
            'Firma',
            'Anrede',
            'Vorname',
            'Name',
            'Straße',
            'Hausnummer',
            'PLZ',
            'Ort',
            'Land',
            'Telefon',
            'Mobil',
            'E-Mail',
            'Homepage',
            'Briefanrede',
            'Bemerkung',
            'Subkategorien',
            'Erstellt am',
            'Aktualisiert am'
        ];
    }

    public function map($aussteller): array
    {
        return [
            $aussteller->firma ?? '',
            $aussteller->anrede ?? '',
            $aussteller->vorname ?? '',
            $aussteller->name ?? '',
            $aussteller->strasse ?? '',
            $aussteller->hausnummer ?? '',
            $aussteller->plz ?? '',
            $aussteller->ort ?? '',
            $aussteller->land ?? '',
            $aussteller->telefon ?? '',
            $aussteller->mobil ?? '',
            $aussteller->email ?? '',
            $aussteller->homepage ?? '',
            $aussteller->briefanrede ?? '',
            $aussteller->bemerkung ?? '',
            $aussteller->subkategorien->pluck('name')->implode(', '),
            $aussteller->created_at?->format('d.m.Y H:i'),
            $aussteller->updated_at?->format('d.m.Y H:i'),
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
                
                // Formatiere E-Mail-Spalte als Hyperlink (Spalte L = Email)
                $emailColumn = 'L';
                for ($row = 2; $row <= $lastRow; $row++) {
                    $email = $sheet->getCell($emailColumn . $row)->getValue();
                    if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        $sheet->getCell($emailColumn . $row)->getHyperlink()->setUrl('mailto:' . $email);
                        $sheet->getStyle($emailColumn . $row)->getFont()->setUnderline(true)->getColor()->setRGB('0000FF');
                    }
                }
                
                // Setze Spaltenbreiten für bestimmte Spalten manuell (optional)
                // Dies überschreibt AutoSize für spezifische Spalten
                $sheet->getColumnDimension('O')->setWidth(50); // Bemerkung
                $sheet->getColumnDimension('P')->setWidth(40); // Subkategorien
            },
        ];
    }
}
