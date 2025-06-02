<?php

namespace App\Services;

use App\Models\Buchung;
use App\Models\Aussteller;
use App\Models\Markt;
use Illuminate\Support\Facades\DB;

class BuchungService
{
    public function createBuchung(array $data)
    {
        return DB::transaction(function () use ($data) {
            // Aussteller erstellen oder aktualisieren
            $aussteller = Aussteller::updateOrCreate(
                ['email' => $data['email']],
                [
                    'firma' => $data['firma'] ?? null,
                    'anrede' => $data['anrede'] ?? null,
                    'vorname' => $data['vorname'],
                    'name' => $data['name'],
                    'strasse' => $data['strasse'],
                    'hausnummer' => $data['hausnummer'] ?? null,
                    'plz' => $data['plz'],
                    'ort' => $data['ort'],
                    'land' => $data['land'],
                    'telefon' => $data['telefon'] ?? null,
                    'mobil' => $data['mobil'] ?? null,
                    'homepage' => $data['homepage'] ?? null,
                    'bemerkung' => $data['bemerkung'] ?? null,
                ]
            );

            // Buchung erstellen
            $buchung = new Buchung([
                'status' => 'anfrage',
                'stand' => $data['stand'],
                'warenangebot' => $data['warenangebot'],
                'herkunft' => $data['herkunft'],
            ]);

            // Beziehungen setzen
            $buchung->markt()->associate(Markt::findOrFail($data['markt']));
            $buchung->aussteller()->associate($aussteller);
            $buchung->save();

            return $buchung;
        });
    }
}
