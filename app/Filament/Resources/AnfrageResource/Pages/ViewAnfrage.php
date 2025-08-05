<?php

namespace App\Filament\Resources\AnfrageResource\Pages;

use App\Filament\Resources\AnfrageResource;
use Filament\Resources\Pages\Page;
use App\Models\Anfrage;
use App\Models\Aussteller;
use App\Models\Medien;
use Filament\Notifications\Notification;
use App\Models\Buchung;
use App\Models\BuchungProtokoll;
use App\Models\BuchungLeistung;
use App\Models\Leistung;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ViewAnfrage extends Page
{
    protected static string $resource = AnfrageResource::class;
    protected static string $view = 'filament.resources.anfrage-resource.pages.view-anfrage';
    protected Anfrage $record;
    public array $matchingAussteller = [];
    public int $anfrageId;
    public array $updateData = [];

    public function mount($record): void
    {
        $this->anfrageId = is_object($record) ? $record->id : $record;
        $this->record = Anfrage::findOrFail($this->anfrageId);
        $this->matchingAussteller = $this->getMatchingAussteller();

        // Checkbox für alle Aussteller mit Unterschied standardmäßig anhaken
        foreach ($this->matchingAussteller as $match) {
            $aus = $match['aussteller'];
            $differences = $this->getAusstellerDifferences($aus);
            if (count($differences) > 0) {
                $this->updateData[$aus->id] = true;
            }
        }
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
        if (!isset($this->record)) {
            return 'Anfrage-Details';
        }

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

    /**
     * Haupt-Action: Buchung für Aussteller anlegen, ggf. Daten übernehmen
     */
    public function createBuchung($ausstellerId)
    {
        $a = $this->getCurrentAnfrage();
        $aussteller = Aussteller::findOrFail($ausstellerId);

        // Prüfe, ob die Checkbox "Geänderte Daten übernehmen" gesetzt ist
        if ($this->updateData[$ausstellerId] ?? false) {
            $this->updateAusstellerFromAnfrage($aussteller, $a);
        }

        $markt = $a->markt;
        $termin = $markt?->termine?->sortBy('start')->first();
        // Wunschstandort verwenden, falls angegeben, sonst ersten verfügbaren Standort
        $standort = $a->wunschStandort ?? $markt?->standorte?->first();
        $maxStandplatz = Buchung::where('termin_id', $termin?->id)
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
            'werbematerial' => $a->werbematerial,
        ]);

        // Gewünschte Zusatzleistungen aus Anfrage importieren
        $this->importLeistungenFromAnfrage($a, $buchung);
        // 'created'-Protokoll löschen, falls direkt importiert
        BuchungProtokoll::where('buchung_id', $buchung->id)
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
            'daten' => $a instanceof Anfrage ? $a->toArray() : [],
        ]);
        // Anfrage als importiert markieren
        $a->importiert = true;
        $a->save();
        Notification::make()
            ->title('Buchung erfolgreich erstellt')
            ->success()
            ->send();

        return $this->redirect(\App\Filament\Resources\BuchungResource::getUrl('edit', ['record' => $buchung->id]));
    }

    /**
     * Nur Aussteller-Daten aktualisieren, keine Buchung
     */
    public function updateAusstellerOnly(int $ausstellerId)
    {
        try {
            $aussteller = Aussteller::findOrFail($ausstellerId);
            $anfrage = $this->getCurrentAnfrage();

            $this->updateAusstellerFromAnfrage($aussteller, $anfrage);

            // Anfrage als importiert markieren
            $anfrage->update(['importiert' => true]);

            Notification::make()
                ->title('Aussteller aktualisiert')
                ->body("Daten von {$aussteller->getFullName()} wurden erfolgreich aktualisiert.")
                ->success()
                ->send();

            // Zur Anfragen-Liste zurück
            return $this->redirect(AnfrageResource::getUrl('index'));
        } catch (\Exception $e) {
            Log::error('Fehler beim Aktualisieren des Ausstellers', [
                'aussteller_id' => $ausstellerId,
                'anfrage_id' => $this->anfrageId,
                'error' => $e->getMessage()
            ]);

            Notification::make()
                ->title('Fehler')
                ->body('Fehler beim Aktualisieren: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    /**
     * Aussteller neu anlegen und Buchung erstellen
     */
    public function ausstellerNeuUndBuchung()
    {
        $a = $this->getCurrentAnfrage();

        // Wenn es gefundene Aussteller gibt, nutze den besten Match
        if (count($this->matchingAussteller) > 0) {
            $bestMatch = $this->matchingAussteller[0]; // Erster ist der beste Match
            $ausstellerId = $bestMatch['aussteller']->id;
            return $this->createBuchung($ausstellerId);
        }

        // Prüfen ob bereits ein Aussteller mit dieser E-Mail existiert (Fallback)
        $existingAussteller = Aussteller::where('email', $a->email)->first();

        if ($existingAussteller) {
            return $this->createBuchung($existingAussteller->id);
        }

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
            'stand' => $a->stand,
            'warenangebot' => $a->warenangebot,
            'herkunft' => $a->herkunft,
            'soziale_medien' => $a->soziale_medien,
        ]);

        // Medien von Anfrage zu Aussteller verschieben
        $this->moveMedienFromAnfrageToAussteller($a, $aus);
        
        // Subkategorien aus Anfrage importieren
        $this->importSubkategorienFromAnfrage($a, $aus);

        return $this->createBuchung($aus->id);
    }

    /**
     * Nur Aussteller neu anlegen, keine Buchung
     */
    public function ausstellerNeuOhneBuchung()
    {
        $a = $this->getCurrentAnfrage();

        // Prüfen ob bereits ein Aussteller mit dieser E-Mail existiert
        $existingAussteller = Aussteller::where('email', $a->email)->first();

        if ($existingAussteller) {
            Notification::make()
                ->title('Aussteller bereits vorhanden')
                ->body("Ein Aussteller mit der E-Mail {$a->email} existiert bereits. Die Daten wurden nicht überschrieben.")
                ->warning()
                ->send();

            return $this->redirect(\App\Filament\Resources\AusstellerResource::getUrl('edit', ['record' => $existingAussteller->id]));
        }

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
            'stand' => $a->stand,
            'warenangebot' => $a->warenangebot,
            'herkunft' => $a->herkunft,
            'soziale_medien' => $a->soziale_medien,
        ]);

        // Medien von Anfrage zu Aussteller verschieben
        $this->moveMedienFromAnfrageToAussteller($a, $aus);
        
        // Subkategorien aus Anfrage importieren
        $this->importSubkategorienFromAnfrage($a, $aus);

        // Anfrage als importiert markieren
        $a->importiert = true;
        $a->save();

        Notification::make()
            ->title('Aussteller erfolgreich angelegt')
            ->body('Der Aussteller wurde ohne Buchung erstellt.')
            ->success()
            ->send();

        return $this->redirect(\App\Filament\Resources\AusstellerResource::getUrl('edit', ['record' => $aus->id]));
    }

    /**
     * Anfrage absagen und E-Mail versenden
     */
    public function ausstellerAbsagen()
    {
        $a = $this->getCurrentAnfrage();

        try {
            // Aussteller-Objekt für MailService erstellen
            $aussteller = new Aussteller();
            $aussteller->email = $a->email;
            $aussteller->vorname = $a->vorname;
            $aussteller->name = $a->name;
            $aussteller->firma = $a->firma;
            $aussteller->warenangebot = $a->warenangebot;

            // Absage-E-Mail über MailService senden
            $mailService = new \App\Services\MailService();
            // Termin-Daten korrekt ermitteln
            $markt = $a->markt;
            $termin = $markt?->termine?->sortBy('start')->first();

            $success = $mailService->sendAusstellerAbsage($aussteller, [
                'markt_name' => $markt->name ?? 'Unbekannter Markt',
                'termin' => $termin && $termin->start ? $termin->start->format('d.m.Y') : 'Unbekanntes Datum',
                'eingereicht_am' => $a->created_at->format('d.m.Y')
            ]);

            if (!$success) {
                throw new \Exception('E-Mail-Versand fehlgeschlagen');
            }

            // Anfrage löschen
            $anfrageId = $a->id;
            $originalEmail = $a->email;
            $a->delete();

            $message = config('mail.dev_redirect_email')
                ? "Die Absage wurde im Testmodus an " . config('mail.dev_redirect_email') . " gesendet (Original: {$originalEmail}) und die Anfrage #{$anfrageId} wurde gelöscht."
                : "Die Absage wurde an {$originalEmail} gesendet und die Anfrage #{$anfrageId} wurde gelöscht.";

            Notification::make()
                ->title('Absage erfolgreich versendet')
                ->body($message)
                ->success()
                ->send();

            // Zurück zur Anfragen-Liste
            return $this->redirect(AnfrageResource::getUrl('index'));
        } catch (\Exception $e) {
            Log::error('Fehler beim Versenden der Absage: ' . $e->getMessage());

            Notification::make()
                ->title('Fehler beim Versenden der Absage')
                ->body('Die Absage konnte nicht versendet werden: ' . $e->getMessage())
                ->danger()
                ->send();

            return false;
        }
    }

    /**
     * Ermittelt Unterschiede zwischen Anfrage und Aussteller
     */
    public function getAusstellerDifferences(Aussteller $aussteller): array
    {
        $anfrage = $this->record;
        $differences = [];

        if ($anfrage->email !== $aussteller->email) {
            $differences[] = "E-Mail: {$anfrage->email} → {$aussteller->email}";
        }
        if ($anfrage->telefon !== $aussteller->telefon) {
            $differences[] = "Telefon: {$anfrage->telefon} → {$aussteller->telefon}";
        }
        if ($anfrage->firma !== $aussteller->firma) {
            $differences[] = "Firma: {$anfrage->firma} → {$aussteller->firma}";
        }
        if ($anfrage->ort !== $aussteller->ort) {
            $differences[] = "Ort: {$anfrage->ort} → {$aussteller->ort}";
        }

        return $differences;
    }

    /**
     * Hilfsmethode: Verschiebt Medien von Anfrage zu Aussteller
     */
    private function moveMedienFromAnfrageToAussteller(Anfrage $anfrage, Aussteller $aussteller): void
    {
        // Alle Medien der Anfrage einfach auf den Aussteller umschreiben
        foreach ($anfrage->medien as $anfragesMedium) {
            $anfragesMedium->update([
                'mediable_type' => Aussteller::class,
                'mediable_id' => $aussteller->id,
            ]);
        }
    }

    /**
     * Hilfsmethode: Aktualisiert Aussteller-Daten aus Anfrage
     */
    private function updateAusstellerFromAnfrage(Aussteller $aussteller, Anfrage $anfrage): void
    {
        // Aussteller-Daten aktualisieren
        $aussteller->update([
            'firma' => $anfrage->firma,
            'anrede' => $anfrage->anrede,
            'vorname' => $anfrage->vorname,
            'name' => $anfrage->nachname,
            'strasse' => $anfrage->strasse,
            'hausnummer' => $anfrage->hausnummer,
            'plz' => $anfrage->plz,
            'ort' => $anfrage->ort,
            'land' => $anfrage->land,
            'telefon' => $anfrage->telefon,
            'email' => $anfrage->email,
            'bemerkung' => $anfrage->bemerkung,
            'stand' => $anfrage->stand,
            'warenangebot' => $anfrage->warenangebot,
            'herkunft' => $anfrage->herkunft,
            'soziale_medien' => $anfrage->soziale_medien,
        ]);

        // Medien von Anfrage zu Aussteller verschieben
        $this->moveMedienFromAnfrageToAussteller($anfrage, $aussteller);
        
        // Subkategorien aus Anfrage importieren
        $this->importSubkategorienFromAnfrage($anfrage, $aussteller);
    }

    /**
     * Importiert gewünschte Zusatzleistungen aus der Anfrage in die Buchung
     */
    private function importLeistungenFromAnfrage(Anfrage $anfrage, Buchung $buchung): void
    {
        // Prüfen ob gewünschte Zusatzleistungen vorhanden sind
        if (!$anfrage->wuensche_zusatzleistungen || !is_array($anfrage->wuensche_zusatzleistungen)) {
            return;
        }

        $sortOrder = 1;

        foreach ($anfrage->wuensche_zusatzleistungen as $leistungId) {
            // Leistung aus Datenbank laden um aktuellen Preis zu bekommen
            $leistung = Leistung::find($leistungId);

            if (!$leistung) {
                Log::warning("Leistung mit ID {$leistungId} nicht gefunden beim Import von Anfrage #{$anfrage->id}");
                continue;
            }

            // BuchungLeistung erstellen
            BuchungLeistung::create([
                'buchung_id' => $buchung->id,
                'leistung_id' => $leistung->id,
                'preis' => $leistung->preis, // Aktueller Preis der Leistung
                'menge' => 1, // Standard-Menge
                'sort' => $sortOrder++,
            ]);

            Log::info("Leistung '{$leistung->name}' (ID: {$leistung->id}) importiert für Buchung #{$buchung->id} aus Anfrage #{$anfrage->id}");
        }
    }

    /**
     * Importiert Subkategorien aus der Anfrage zum Aussteller
     */
    private function importSubkategorienFromAnfrage(Anfrage $anfrage, Aussteller $aussteller): void
    {
        // Prüfen ob Warenangebot (Subkategorien) vorhanden sind
        if (!$anfrage->warenangebot || !is_array($anfrage->warenangebot)) {
            return;
        }

        $validSubkategorieIds = [];

        foreach ($anfrage->warenangebot as $subkategorieId) {
            // Prüfen ob Subkategorie existiert
            if (\App\Models\Subkategorie::find($subkategorieId)) {
                $validSubkategorieIds[] = $subkategorieId;
            } else {
                Log::warning("Subkategorie mit ID {$subkategorieId} nicht gefunden beim Import von Anfrage #{$anfrage->id}");
            }
        }

        if (!empty($validSubkategorieIds)) {
            // Subkategorien dem Aussteller zuordnen
            $aussteller->subkategorien()->sync($validSubkategorieIds);
            
            Log::info("Subkategorien [" . implode(', ', $validSubkategorieIds) . "] importiert für Aussteller #{$aussteller->id} aus Anfrage #{$anfrage->id}");
        }
    }
}
