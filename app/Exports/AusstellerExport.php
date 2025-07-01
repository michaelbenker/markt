<?php

namespace App\Exports;

use App\Models\Aussteller;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Database\Eloquent\Collection;

class AusstellerExport implements FromCollection, WithHeadings, WithMapping, WithStyles
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
            // Header-Zeile fett machen
            1 => ['font' => ['bold' => true]],

            // Auto-Width für alle Spalten
            'A:R' => ['alignment' => ['wrap_text' => true]],
        ];
    }
}
