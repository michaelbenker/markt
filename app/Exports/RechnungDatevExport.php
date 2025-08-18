<?php

namespace App\Exports;

use App\Models\Rechnung;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Database\Eloquent\Collection;
use Carbon\Carbon;

class RechnungDatevExport implements FromArray, WithStyles
{
    protected Collection $records;
    protected string $von;
    protected string $bis;
    protected string $beraternummer;
    protected string $mandantennummer;
    protected string $exportKuerzel;

    public function __construct(
        Collection $records,
        ?string $von = null,
        ?string $bis = null,
        string $beraternummer = '569230',
        string $mandantennummer = '20117',
        string $exportKuerzel = 'uv'
    ) {
        $this->records = $records;
        $this->von = $von ?: now()->startOfMonth()->format('Ymd');
        $this->bis = $bis ?: now()->endOfMonth()->format('Ymd');
        $this->beraternummer = $beraternummer;
        $this->mandantennummer = $mandantennummer;
        $this->exportKuerzel = $exportKuerzel;
    }

    public function array(): array
    {
        $data = [];
        
        // Zeile 1: Header-Zeile mit fixen Werten
        $headerRow = $this->getHeaderRow();
        $data[] = $headerRow;
        
        // Zeile 2: DATEV-Feldbezeichnungen
        $data[] = $this->getDatevFieldNames();
        
        // Ab Zeile 3: Daten
        foreach ($this->records as $rechnung) {
            $data[] = $this->mapRechnung($rechnung);
        }
        
        return $data;
    }

    protected function getHeaderRow(): array
    {
        $now = now();
        $jahr = $now->format('Y');
        
        $row = array_fill(0, 116, ''); // 116 Spalten (A bis DL)
        
        // Feste Header-Werte
        $row[0] = 'EXTF'; // A
        $row[1] = '510'; // B
        $row[2] = '21'; // C
        $row[3] = 'Buchungsstapel'; // D
        $row[4] = '7'; // E
        $row[5] = $now->format('YmdHis') . '000'; // F - Erstellungsdatum
        $row[6] = ''; // G - wird leer gelassen
        $row[7] = ''; // H
        $row[8] = ''; // I
        $row[9] = $this->exportKuerzel; // J - Kürzel Export
        $row[10] = $this->beraternummer; // K - Beraternummer
        $row[11] = $this->mandantennummer; // L - Mandantennummer
        $row[12] = $jahr . '0101'; // M - Buchungsjahr (Beginn)
        $row[13] = '4'; // N - Sachkontenlänge
        $row[14] = $this->von; // O - Buchungszeitraum von
        $row[15] = $this->bis; // P - Buchungszeitraum bis
        $row[16] = ''; // Q
        $row[17] = ''; // R
        $row[18] = '1'; // S
        $row[19] = ''; // T
        $row[20] = '1'; // U
        $row[21] = 'EUR'; // V - Währung
        
        return $row;
    }

    protected function getDatevFieldNames(): array
    {
        $fields = array_fill(0, 116, '');
        
        $fields[0] = 'Umsatz (ohne Soll-/Haben-Kennzeichen)'; // A
        $fields[1] = 'Soll-/Haben-Kennzeichen'; // B
        $fields[2] = 'WKZ Umsatz'; // C
        $fields[3] = 'Kurs'; // D
        $fields[4] = 'Basisumsatz'; // E
        $fields[5] = 'WKZ Basisumsatz'; // F
        $fields[6] = 'Konto'; // G
        $fields[7] = 'Gegenkonto (ohne BU-Schlüssel)'; // H
        $fields[8] = 'BU-Schlüssel'; // I
        $fields[9] = 'Belegdatum'; // J
        $fields[10] = 'Belegfeld 1'; // K
        $fields[11] = 'Belegfeld 2'; // L
        $fields[12] = 'Skonto'; // M
        $fields[13] = 'Buchungstext'; // N
        $fields[14] = 'Postensperre'; // O
        $fields[15] = 'Diverse Adressnummer'; // P
        $fields[16] = 'Geschäftspartnerbank'; // Q
        $fields[17] = 'Sachverhalt'; // R
        $fields[18] = 'Zinssperre'; // S
        $fields[19] = 'Beleglink'; // T
        $fields[20] = 'Beleginfo - Art 1'; // U
        $fields[21] = 'Beleginfo - Inhalt 1'; // V
        $fields[22] = 'Beleginfo - Art 2'; // W
        $fields[23] = 'Beleginfo - Inhalt 2'; // X
        $fields[24] = 'Beleginfo - Art 3'; // Y
        $fields[25] = 'Beleginfo - Inhalt 3'; // Z
        $fields[26] = 'Beleginfo - Art 4'; // AA
        $fields[27] = 'Beleginfo - Inhalt 4'; // AB
        $fields[28] = 'Beleginfo - Art 5'; // AC
        $fields[29] = 'Beleginfo - Inhalt 5'; // AD
        $fields[30] = 'Beleginfo - Art 6'; // AE
        $fields[31] = 'Beleginfo - Inhalt 6'; // AF
        $fields[32] = 'Beleginfo - Art 7'; // AG
        $fields[33] = 'Beleginfo - Inhalt 7'; // AH
        $fields[34] = 'Beleginfo - Art 8'; // AI
        $fields[35] = 'Beleginfo - Inhalt 8'; // AJ
        $fields[36] = 'KOST1 - Kostenstelle'; // AK
        $fields[37] = 'KOST2 - Kostenstelle'; // AL
        $fields[38] = 'Kost-Menge'; // AM
        $fields[39] = 'EU-Mitgliedstaat u. USt-IdNr.'; // AN
        $fields[40] = 'EU-Steuersatz'; // AO
        $fields[41] = 'Abw. Versteuerungsart'; // AP
        $fields[42] = 'Sachverhalt L+L'; // AQ
        $fields[43] = 'Funktionsergänzung L+L'; // AR
        $fields[44] = 'BU 49 Hauptfunktionstyp'; // AS
        $fields[45] = 'BU 49 Hauptfunktionsnummer'; // AT
        $fields[46] = 'BU 49 Funktionsergänzung'; // AU
        $fields[47] = 'Zusatzinformation - Art 1'; // AV
        $fields[48] = 'Zusatzinformation - Inhalt 1'; // AW
        $fields[49] = 'Zusatzinformation - Art 2'; // AX
        $fields[50] = 'Zusatzinformation - Inhalt 2'; // AY
        $fields[51] = 'Zusatzinformation - Art 3'; // AZ
        $fields[52] = 'Zusatzinformation - Inhalt 3'; // BA
        $fields[53] = 'Zusatzinformation - Art 4'; // BB
        $fields[54] = 'Zusatzinformation - Inhalt 4'; // BC
        $fields[55] = 'Zusatzinformation - Art 5'; // BD
        $fields[56] = 'Zusatzinformation - Inhalt 5'; // BE
        $fields[57] = 'Zusatzinformation - Art 6'; // BF
        $fields[58] = 'Zusatzinformation - Inhalt 6'; // BG
        $fields[59] = 'Zusatzinformation - Art 7'; // BH
        $fields[60] = 'Zusatzinformation - Inhalt 7'; // BI
        $fields[61] = 'Zusatzinformation - Art 8'; // BJ
        $fields[62] = 'Zusatzinformation - Inhalt 8'; // BK
        $fields[63] = 'Zusatzinformation - Art 9'; // BL
        $fields[64] = 'Zusatzinformation - Inhalt 9'; // BM
        $fields[65] = 'Zusatzinformation - Art 10'; // BN
        $fields[66] = 'Zusatzinformation - Inhalt 10'; // BO
        $fields[67] = 'Zusatzinformation - Art 11'; // BP
        $fields[68] = 'Zusatzinformation - Inhalt 11'; // BQ
        $fields[69] = 'Zusatzinformation - Art 12'; // BR
        $fields[70] = 'Zusatzinformation - Inhalt 12'; // BS
        $fields[71] = 'Zusatzinformation - Art 13'; // BT
        $fields[72] = 'Zusatzinformation - Inhalt 13'; // BU
        $fields[73] = 'Zusatzinformation - Art 14'; // BV
        $fields[74] = 'Zusatzinformation - Inhalt 14'; // BW
        $fields[75] = 'Zusatzinformation - Art 15'; // BX
        $fields[76] = 'Zusatzinformation - Inhalt 15'; // BY
        $fields[77] = 'Zusatzinformation - Art 16'; // BZ
        $fields[78] = 'Zusatzinformation - Inhalt 16'; // CA
        $fields[79] = 'Zusatzinformation - Art 17'; // CB
        $fields[80] = 'Zusatzinformation - Inhalt 17'; // CC
        $fields[81] = 'Zusatzinformation - Art 18'; // CD
        $fields[82] = 'Zusatzinformation - Inhalt 18'; // CE
        $fields[83] = 'Zusatzinformation - Art 19'; // CF
        $fields[84] = 'Zusatzinformation - Inhalt 19'; // CG
        $fields[85] = 'Zusatzinformation - Art 20'; // CH
        $fields[86] = 'Zusatzinformation - Inhalt 20'; // CI
        $fields[87] = 'Stück'; // CJ
        $fields[88] = 'Gewicht'; // CK
        $fields[89] = 'Zahlweise'; // CL
        $fields[90] = 'Forderungsart'; // CM
        $fields[91] = 'Veranlagungsjahr'; // CN
        $fields[92] = 'Zugeordnete Fälligkeit'; // CO
        $fields[93] = 'Skontotyp'; // CP
        $fields[94] = 'Auftragsnummer'; // CQ
        $fields[95] = 'Buchungstyp'; // CR
        $fields[96] = 'Ust-Schlüssel (Anzahlungen)'; // CS
        $fields[97] = 'EU-Mitgliedstaat (Anzahlungen)'; // CT
        $fields[98] = 'Sachverhalt L+L (Anzahlungen)'; // CU
        $fields[99] = 'EU-Steuersatz (Anzahlungen)'; // CV
        $fields[100] = 'Erlöskonto (Anzahlungen)'; // CW
        $fields[101] = 'Herkunft-Kz'; // CX
        $fields[102] = 'Leerfeld'; // CY
        $fields[103] = 'KOST-Datum'; // CZ
        $fields[104] = 'SEPA-Mandatsreferenz'; // DA
        $fields[105] = 'Skontosperre'; // DB
        $fields[106] = 'Gesellschaftername'; // DC
        $fields[107] = 'Beteiligtennummer'; // DD
        $fields[108] = 'Identifikationsnummer'; // DE
        $fields[109] = 'Zeichnernummer'; // DF
        $fields[110] = 'Postensperre bis'; // DG
        $fields[111] = 'Bezeichnung SoBil-Sachverhalt'; // DH
        $fields[112] = 'Kennzeichen SoBil-Buchung'; // DI
        $fields[113] = 'Festschreibung'; // DJ
        $fields[114] = 'Leistungsdatum'; // DK
        $fields[115] = 'Datum Zuord.Steuerperiode'; // DL
        
        return $fields;
    }

    protected function mapRechnung($rechnung): array
    {
        $rechnung->load(['aussteller', 'buchung.markt']);
        
        $row = array_fill(0, 116, '');
        
        // A: Umsatz (Bruttobetrag) - ohne Vorzeichen, in Cent-Notation (123,45 € = 12345)
        $row[0] = number_format($rechnung->bruttobetrag / 100, 2, ',', '');
        
        // B: Soll-/Haben-Kennzeichen - S für Rechnung, H für Gutschrift
        $row[1] = $rechnung->bruttobetrag >= 0 ? 'S' : 'H';
        
        // C: WKZ Umsatz - leer lassen (EUR ist Standard)
        $row[2] = '';
        
        // D: Kurs - leer lassen
        $row[3] = '';
        
        // E: Basisumsatz - leer lassen
        $row[4] = '';
        
        // F: WKZ Basisumsatz - leer lassen
        $row[5] = '';
        
        // G: Konto (Debitorennummer) - Aussteller-ID verwenden
        $row[6] = $rechnung->aussteller_id ? str_pad($rechnung->aussteller_id, 5, '0', STR_PAD_LEFT) : '';
        
        // H: Gegenkonto - Sachkonto 4403 für Märkte
        $row[7] = '4403';
        
        // I: BU-Schlüssel - leer lassen
        $row[8] = '';
        
        // J: Belegdatum (TTMM Format)
        $belegdatum = Carbon::parse($rechnung->rechnungsdatum);
        $row[9] = $belegdatum->format('dm');
        
        // K: Belegfeld 1 - Rechnungsnummer
        $row[10] = $rechnung->rechnungsnummer;
        
        // L: Belegfeld 2 - Fälligkeitsdatum
        $faelligkeitsdatum = Carbon::parse($rechnung->faelligkeitsdatum);
        $row[11] = $faelligkeitsdatum->format('d.m.Y');
        
        // M: Skonto - leer lassen
        $row[12] = '';
        
        // N: Buchungstext
        $buchungstext = $rechnung->betreff;
        if ($rechnung->buchung && $rechnung->buchung->markt) {
            $buchungstext = $rechnung->buchung->markt->name . ' - ' . $buchungstext;
        }
        $row[13] = substr($buchungstext, 0, 60); // Max 60 Zeichen
        
        // O-AJ: Diverse Felder - leer lassen
        for ($i = 14; $i <= 35; $i++) {
            $row[$i] = '';
        }
        
        // AK: KOST1 - Kostenstelle basierend auf Markt
        if ($rechnung->buchung && $rechnung->buchung->markt) {
            $marktName = strtolower($rechnung->buchung->markt->name);
            if (strpos($marktName, 'tuk') !== false || strpos($marktName, 'töpfer') !== false) {
                $row[36] = '805'; // TuK
            } elseif (strpos($marktName, 'aif') !== false || strpos($marktName, 'auer') !== false) {
                $row[36] = '811'; // AiF
            } elseif (strpos($marktName, 'kirta') !== false || strpos($marktName, 'kirchweih') !== false) {
                $row[36] = '806'; // Kirta
            } else {
                $row[36] = '805'; // Default: TuK
            }
        } else {
            $row[36] = '805'; // Default: TuK
        }
        
        // AL-DI: Weitere Felder - leer lassen
        for ($i = 37; $i <= 112; $i++) {
            $row[$i] = '';
        }
        
        // DJ: Festschreibung - immer 1
        $row[113] = '1';
        
        // DK: Leistungsdatum
        if ($rechnung->lieferdatum) {
            $lieferdatum = Carbon::parse($rechnung->lieferdatum);
            $row[114] = $lieferdatum->format('d.m.Y');
        } else {
            $row[114] = '';
        }
        
        // DL: Datum Zuord.Steuerperiode - leer lassen
        $row[115] = '';
        
        return $row;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Keine speziellen Styles für DATEV-Export
        ];
    }
}