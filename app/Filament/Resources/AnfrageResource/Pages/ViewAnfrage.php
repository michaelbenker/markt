<?php

namespace App\Filament\Resources\AnfrageResource\Pages;

use App\Filament\Resources\AnfrageResource;
use Filament\Resources\Pages\Page;
use App\Models\Anfrage;
use App\Models\Aussteller;
use Filament\Notifications\Notification;
use App\Models\Buchung;
use App\Models\BuchungProtokoll;
use Illuminate\Support\Facades\Auth;

class ViewAnfrage extends Page
{
    protected static string $resource = AnfrageResource::class;
    protected static string $view = 'filament.resources.anfrage-resource.pages.view-anfrage';
    protected Anfrage $record;
    public array $matchingAussteller = [];
    public int $anfrageId;

    public function mount($record): void
    {
        $this->anfrageId = is_object($record) ? $record->id : $record;
        $this->record = Anfrage::findOrFail($this->anfrageId);
        $this->matchingAussteller = $this->getMatchingAussteller();
    }

    public function getMatchingAussteller(): array
    {
        $a = $this->record;
        $matches = [];
        $aussteller = Aussteller::all();
        foreach ($aussteller as $aus) {
            $score = 0;
            $criteria = [];
            $isPerfectMatch = false;
            // Firmenname, Vorname, Nachname, Stadt, E-Mail
            if (
                $a->firma && $aus->firma && $a->firma === $aus->firma &&
                $a->vorname && $aus->vorname && $a->vorname === $aus->vorname &&
                $a->nachname && $aus->name && $a->nachname === $aus->name &&
                $a->ort && $aus->ort && $a->ort === $aus->ort &&
                $a->email && $aus->email && $a->email === $aus->email
            ) {
                $isPerfectMatch = true;
            }
            // Vorname, Nachname, Stadt, E-Mail (ohne Firma)
            if (
                !$a->firma && !$aus->firma &&
                $a->vorname && $aus->vorname && $a->vorname === $aus->vorname &&
                $a->nachname && $aus->name && $a->nachname === $aus->name &&
                $a->ort && $aus->ort && $a->ort === $aus->ort &&
                $a->email && $aus->email && $a->email === $aus->email
            ) {
                $isPerfectMatch = true;
            }
            // Firmenname und Stadt
            if ($a->firma && $aus->firma && $a->firma === $aus->firma && $a->ort && $aus->ort && $a->ort === $aus->ort) {
                $score += 50;
                $criteria[] = 'Firma & Stadt';
            }
            // Firmenname und Email
            if ($a->firma && $aus->firma && $a->firma === $aus->firma && $a->email && $aus->email && $a->email === $aus->email) {
                $score += 50;
                $criteria[] = 'Firma & E-Mail';
            }
            // Vorname, Nachname und Email
            if ($a->vorname && $aus->vorname && $a->vorname === $aus->vorname && $a->nachname && $aus->name && $a->nachname === $aus->name && $a->email && $aus->email && $a->email === $aus->email) {
                $score += 50;
                $criteria[] = 'Vorname, Nachname & E-Mail';
            }
            // Vorname, Nachname und Stadt
            if ($a->vorname && $aus->vorname && $a->vorname === $aus->vorname && $a->nachname && $aus->name && $a->nachname === $aus->name && $a->ort && $aus->ort && $a->ort === $aus->ort) {
                $score += 50;
                $criteria[] = 'Vorname, Nachname & Stadt';
            }
            // Optional: Telefon
            if ($a->telefon && $aus->telefon && $a->telefon === $aus->telefon) {
                $score += 30;
                $criteria[] = 'Telefon';
            }
            // Optional: PLZ + Straße
            if ($a->plz && $aus->plz && $a->plz === $aus->plz && $a->strasse && $aus->strasse && $a->strasse === $aus->strasse) {
                $score += 30;
                $criteria[] = 'PLZ & Straße';
            }
            if ($score > 0) {
                $matches[] = [
                    'aussteller' => $aus,
                    'score' => $score,
                    'criteria' => $criteria,
                    'perfect' => $isPerfectMatch,
                ];
            }
        }
        // Sortiere nach Score absteigend
        usort($matches, fn($a, $b) => $b['score'] <=> $a['score']);
        return $matches;
    }

    public function getTitle(): string
    {
        $markt = $this->record->markt;
        if (!$markt) {
            return 'Anfrage-Details';
        }
        $name = $markt->name;
        $termine = $markt->termine?->map(function ($t) {
            return \Carbon\Carbon::parse($t->start)->format('d.m.Y');
        })->toArray() ?? [];
        $termineStr = count($termine) ? ' (' . implode(', ', $termine) . ')' : '';
        return "Anfrage: " . $name . $termineStr;
    }

    protected function getCurrentAnfrage()
    {
        return Anfrage::findOrFail($this->anfrageId);
    }

    public function createBuchung($ausstellerId)
    {
        $a = $this->getCurrentAnfrage();
        $markt = $a->markt;
        $termin = $markt?->termine?->sortBy('start')->first();
        $standort = $markt?->standorte?->first();
        $maxStandplatz = \App\Models\Buchung::where('termin_id', $termin?->id)
            ->where('standort_id', $standort?->id)
            ->max('standplatz');
        $nextStandplatz = $maxStandplatz ? ((int)$maxStandplatz + 1) : 1;
        $buchung = Buchung::create([
            'status' => 'bearbeitung',
            'termin_id' => $termin?->id,
            'standort_id' => $standort?->id,
            'standplatz' => $nextStandplatz,
            'aussteller_id' => $ausstellerId,
            'stand' => $a->stand,
            'warenangebot' => $a->warenangebot,
            'herkunft' => $a->herkunft,
        ]);
        // 'created'-Protokoll löschen, falls direkt importiert
        \App\Models\BuchungProtokoll::where('buchung_id', $buchung->id)
            ->where('aktion', 'created')
            ->latest()
            ->first()?->delete();
        // Protokoll-Eintrag für Import
        BuchungProtokoll::create([
            'buchung_id' => $buchung->id,
            'user_id' => Auth::id(),
            'aktion' => 'import_anfrage',
            'from_status' => 'anfrage',
            'to_status' => 'bearbeitung',
            'details' => 'Buchung wurde aus Anfrage #' . $a->id . ' importiert.',
            'daten' => $a instanceof \App\Models\Anfrage ? $a->toArray() : [],
        ]);
        // Anfrage als importiert markieren
        $a->importiert = true;
        $a->save();
        Notification::make()
            ->title('Buchung erfolgreich erstellt')
            ->success()
            ->send();
        return redirect()->route('filament.admin.resources.buchung.edit', ['record' => $buchung->id]);
    }

    public function updateAusstellerUndBuchung($ausstellerId)
    {
        $a = $this->getCurrentAnfrage();
        $aus = Aussteller::findOrFail($ausstellerId);
        $aus->update([
            'firma' => $a->firma,
            'anrede' => $a->anrede,
            'vorname' => $a->vorname,
            'name' => $a->nachname,
            'strasse' => $a->strasse,
            'hausnummer' => $a->hausnummer,
            'plz' => $a->plz,
            'ort' => $a->ort,
            'land' => $a->land,
            'telefon' => $a->telefon,
            'email' => $a->email,
            'bemerkung' => $a->bemerkung,
        ]);
        return $this->createBuchung($ausstellerId);
    }

    public function buchungMitDatenUebernehmen($ausstellerId)
    {
        // Keine Änderung am Aussteller, nur Buchung anlegen
        return $this->createBuchung($ausstellerId);
    }

    public function ausstellerNeuUndBuchung()
    {
        $a = $this->getCurrentAnfrage();
        $aus = Aussteller::create([
            'firma' => $a->firma,
            'anrede' => $a->anrede,
            'vorname' => $a->vorname,
            'name' => $a->nachname,
            'strasse' => $a->strasse,
            'hausnummer' => $a->hausnummer,
            'plz' => $a->plz,
            'ort' => $a->ort,
            'land' => $a->land,
            'telefon' => $a->telefon,
            'email' => $a->email,
            'bemerkung' => $a->bemerkung,
        ]);
        return $this->createBuchung($aus->id);
    }

    public function ausstellerNeuOhneBuchung()
    {
        $a = $this->getCurrentAnfrage();
        $aus = Aussteller::create([
            'firma' => $a->firma,
            'anrede' => $a->anrede,
            'vorname' => $a->vorname,
            'name' => $a->nachname,
            'strasse' => $a->strasse,
            'hausnummer' => $a->hausnummer,
            'plz' => $a->plz,
            'ort' => $a->ort,
            'land' => $a->land,
            'telefon' => $a->telefon,
            'email' => $a->email,
            'bemerkung' => $a->bemerkung,
        ]);

        // Anfrage als importiert markieren
        $a->importiert = true;
        $a->save();

        Notification::make()
            ->title('Aussteller erfolgreich angelegt')
            ->body('Der Aussteller wurde ohne Buchung erstellt.')
            ->success()
            ->send();

        return redirect()->route('filament.admin.resources.aussteller.edit', ['record' => $aus->id]);
    }
}
