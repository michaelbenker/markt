<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Aussteller;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\HeadingRowImport;
use App\Imports\AusstellerImport;

class AusstellerImportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $filePath = storage_path('app/import/aussteller.xlsx');
        $stats = [
            'total' => 0,
            'skipped' => 0,
            'imported' => 0,
            'errors' => 0
        ];

        $import = new AusstellerImport();
        $rows = Excel::toArray($import, $filePath)[0];
        $stats['total'] = count($rows);

        // Logge die Keys der ersten Zeile
        if (isset($rows[0])) {
            Log::info('Spaltennamen (Keys) der ersten Zeile:', array_keys($rows[0]));
        }

        foreach ($rows as $index => $row) {
            try {
                // 1. Überspringe nur, wenn weder 'firma' noch 'name' vorhanden ist
                if (empty($row['firma']) && empty($row['name'])) {
                    Log::info("Zeile {$index}: Übersprungen - Weder Firma noch Name vorhanden", [
                        'firma' => $row['firma'] ?? 'Unbekannt',
                        'name' => $row['name'] ?? 'Unbekannt'
                    ]);
                    $stats['skipped']++;
                    continue;
                }

                // 2. Homepage prüfen und ggf. anpassen
                $homepage = $row['homepage'] ?? null;
                if ($homepage) {
                    $homepage = trim($homepage);
                    if (str_starts_with($homepage, 'https://')) {
                        // nichts tun
                    } elseif (str_starts_with($homepage, 'www.')) {
                        $homepage = 'https://' . $homepage;
                    }
                    // sonst bleibt wie sie ist
                }

                Aussteller::create([
                    'firma' => $row['firma'] ?? null,
                    'anrede' => $row['anrede'] ?? null,
                    'vorname' => $row['vorname'] ?? null,
                    'name' => $row['name'] ?? null,
                    'strasse' => $row['strasse'] ?? null,
                    'hausnummer' => $row['hausnummer'] ?? null,
                    'plz' => $row['plz'] ?? null,
                    'ort' => $row['ort'] ?? null,
                    'land' => 'Deutschland',
                    'telefon' => $row['telefon'] ?? null,
                    'mobil' => $row['mobile'] ?? null,
                    'homepage' => $homepage,
                    'email' => $row['email'] ?? null,
                    'briefanrede' => $row['briefanrede'] ?? null,
                    'bemerkung' => $row['bemerkung'] ?? null,
                ]);

                Log::info("Zeile {$index}: Erfolgreich importiert", [
                    'firma' => $row['firma'] ?? 'Unbekannt',
                    'email' => $row['email']
                ]);
                $stats['imported']++;
            } catch (\Exception $e) {
                Log::error("Zeile {$index}: Fehler beim Import", [
                    'firma' => $row['firma'] ?? 'Unbekannt',
                    'error' => $e->getMessage()
                ]);
                $stats['errors']++;
            }
        }

        // Zusammenfassung ausgeben
        Log::info("Import Zusammenfassung", [
            'Gesamt Datensätze' => $stats['total'],
            'Erfolgreich importiert' => $stats['imported'],
            'Übersprungen' => $stats['skipped'],
            'Fehler' => $stats['errors']
        ]);
    }
}
