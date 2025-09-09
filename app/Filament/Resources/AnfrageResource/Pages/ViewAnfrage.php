<?php

namespace App\Filament\Resources\AnfrageResource\Pages;

use App\Filament\Resources\AnfrageResource;
use Filament\Resources\Pages\ViewRecord;
use Parallax\FilamentComments\Actions\CommentsAction;
use App\Models\Anfrage;
use App\Models\Aussteller;
use Filament\Notifications\Notification;
use App\Models\Buchung;
use App\Models\BuchungProtokoll;
use App\Models\BuchungLeistung;
use App\Models\Leistung;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ViewAnfrage extends ViewRecord
{
    protected static string $resource = AnfrageResource::class;
    protected static string $view = 'filament.resources.anfrage-resource.pages.view-anfrage';
    public array $matchingAussteller = [];
    public int $anfrageId;
    public array $updateData = [];
    public array $selectedTags = [];
    public ?int $selectedRating = 0;
    public string $tagsJson = '{}';
    public $termineObjects = [];

    public function mount($record): void
    {
        parent::mount($record);

        $this->anfrageId = $this->record->id;
        // Als Array speichern für Livewire
        // $this->record->termine gibt bereits die Termin-Objekte zurück (durch den Accessor)
        $termine = $this->record->termine;

        $this->termineObjects = $termine->map(function ($termin) {
            return [
                'id' => $termin->id,
                'start' => $termin->start->format('d.m.Y'),
                'ende' => $termin->ende ? $termin->ende->format('d.m.Y') : null,
            ];
        })->toArray();

        $this->matchingAussteller = $this->getMatchingAussteller();

        // Checkbox für alle Aussteller mit Unterschied standardmäßig anhaken
        foreach ($this->matchingAussteller as $match) {
            $aus = $match['aussteller'];
            $differences = $this->getAusstellerDifferences($aus);
            if (count($differences) > 0) {
                $this->updateData[$aus->id] = true;
            }
        }

        // Tags als JSON für JavaScript vorbereiten (ohne Icons, nur Namen)
        $tags = \App\Models\Tag::all()->mapWithKeys(function ($tag) {
            return [$tag->id => $tag->name];
        });
        $this->tagsJson = json_encode($tags);
    }

    protected function getHeaderActions(): array
    {
        return [
            CommentsAction::make()
                ->label('Kommentare')
        ];
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

        return "Anfrage: " . $name;
    }

    public function getWarenangebotText(): string
    {
        $anfrage = $this->record;

        if (!is_array($anfrage->warenangebot)) {
            return $anfrage->warenangebot ?? '';
        }

        // Neue Struktur mit subkategorien
        if (isset($anfrage->warenangebot['subkategorien'])) {
            $subkategorienIds = $anfrage->warenangebot['subkategorien'];
            $sonstiges = $anfrage->warenangebot['sonstiges'] ?? null;

            $namen = [];
            if (!empty($subkategorienIds)) {
                $namen = \App\Models\Subkategorie::whereIn('id', $subkategorienIds)->pluck('name')->toArray();

                // Wenn Sonstiges vorhanden und ID 24 in den Subkategorien ist, füge den Text hinzu
                if ($sonstiges && in_array(24, $subkategorienIds)) {
                    $namen[] = "Sonstiges: " . $sonstiges;
                }
            } elseif ($sonstiges) {
                $namen[] = "Sonstiges: " . $sonstiges;
            }

            return implode(', ', $namen);
        }

        // Alte Struktur (falls vorhanden)
        return implode(', ', $anfrage->warenangebot);
    }

    protected function getCurrentAnfrage()
    {
        return $this->record;
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
        // Wunschstandort verwenden, falls angegeben, sonst ersten verfügbaren Standort
        $standort = $a->wunschStandort ?? $markt?->standorte?->first();

        // Standplatz bestimmen - verwende ersten Termin für die Berechnung
        $ersterTermin = $a->termine->first();
        $maxStandplatz = Buchung::whereJsonContains('termine', $ersterTermin?->id)
            ->where('standort_id', $standort?->id)
            ->max('standplatz');
        $nextStandplatz = $maxStandplatz ? ((int)$maxStandplatz + 1) : 1;

        // Termin-IDs aus der Anfrage übernehmen
        $terminIds = $a->termine->pluck('id')->toArray();

        $buchung = Buchung::create([
            'status' => 'bearbeitung',
            'markt_id' => $markt?->id,
            'termine' => $terminIds, // Alle Termine aus der Anfrage
            'standort_id' => $standort?->id,
            'standplatz' => $nextStandplatz,
            'aussteller_id' => $ausstellerId,
            'stand' => $a->stand,
            'warenangebot' => $a->warenangebot,
            'herkunft' => $a->herkunft,
            'werbematerial' => $a->werbematerial,
            'bemerkung' => $a->bemerkung,  // Bemerkung aus Anfrage in Buchung übernehmen
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
        // Anfrage Status auf gebucht setzen
        $a->status = 'gebucht';
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

            // Anfrage Status auf aussteller_importiert setzen
            $anfrage->update(['status' => 'aussteller_importiert']);

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

        // Nur Sonstiges-Text aus Warenangebot in die Aussteller-Bemerkung
        $ausstellerBemerkung = null;
        if (is_array($a->warenangebot) && isset($a->warenangebot['sonstiges'])) {
            $ausstellerBemerkung = "Sonstiges Warenangebot: " . $a->warenangebot['sonstiges'];
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
            'bemerkung' => $ausstellerBemerkung,  // Nur Sonstiges, nicht die Anfrage-Bemerkung
            'steuer_id' => $a->steuer_id,
            'handelsregisternummer' => $a->handelsregisternummer,
            'stand' => $a->stand,
            'warenangebot' => $a->warenangebot,
            'vorfuehrung_am_stand' => $a->vorfuehrung_am_stand,
            'herkunft' => $a->herkunft,
            'soziale_medien' => $a->soziale_medien,
            'rating' => $this->selectedRating ?? 0,  // Rating übernehmen
        ]);

        // Medien von Anfrage zu Aussteller verschieben
        $this->moveMedienFromAnfrageToAussteller($a, $aus);

        // Subkategorien aus Anfrage importieren
        $this->importSubkategorienFromAnfrage($a, $aus);

        // Tags hinzufügen, wenn welche ausgewählt wurden
        if (!empty($this->selectedTags)) {
            $aus->tags()->attach($this->selectedTags);
        }

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

        // Bemerkung mit Markt-Information erstellen
        $ausstellerBemerkung = "Bewerbung für: " . ($a->markt->name ?? 'Unbekannter Markt');
        if (is_array($a->warenangebot) && isset($a->warenangebot['sonstiges'])) {
            $ausstellerBemerkung .= "\nSonstiges Warenangebot: " . $a->warenangebot['sonstiges'];
        }
        if ($a->bemerkung) {
            $ausstellerBemerkung .= "\nAnfrage-Bemerkung: " . $a->bemerkung;
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
            'bemerkung' => $ausstellerBemerkung,
            'steuer_id' => $a->steuer_id,
            'handelsregisternummer' => $a->handelsregisternummer,
            'stand' => $a->stand,
            'warenangebot' => $a->warenangebot,
            'vorfuehrung_am_stand' => $a->vorfuehrung_am_stand,
            'herkunft' => $a->herkunft,
            'soziale_medien' => $a->soziale_medien,
            'rating' => $this->selectedRating ?? 0,  // Rating übernehmen
        ]);

        // Medien von Anfrage zu Aussteller verschieben
        $this->moveMedienFromAnfrageToAussteller($a, $aus);

        // Subkategorien aus Anfrage importieren
        $this->importSubkategorienFromAnfrage($a, $aus);

        // Tags hinzufügen, wenn welche ausgewählt wurden
        if (!empty($this->selectedTags)) {
            $aus->tags()->attach($this->selectedTags);
        }

        // Anfrage Status auf aussteller_importiert setzen
        $a->status = 'aussteller_importiert';
        $a->save();

        // E-Mail senden
        $mailService = new \App\Services\MailService();
        $result = $mailService->sendAnfrageAusstellerImportiert($a);

        if ($result) {
            Notification::make()
                ->title('Aussteller erfolgreich angelegt')
                ->body('Der Aussteller wurde ohne Buchung erstellt und eine Benachrichtigungs-E-Mail wurde versendet.')
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title('Aussteller angelegt')
                ->body('Der Aussteller wurde erstellt, aber die E-Mail konnte nicht versendet werden.')
                ->warning()
                ->send();
        }

        return $this->redirect(\App\Filament\Resources\AusstellerResource::getUrl('edit', ['record' => $aus->id]));
    }

    /**
     * Anfrage auf Warteliste setzen
     */
    public function aufWartelisteSetzen()
    {
        $anfrage = $this->getCurrentAnfrage();

        // Status auf warteschlange setzen
        $anfrage->update(['status' => 'warteschlange']);

        // E-Mail senden
        $mailService = new \App\Services\MailService();

        // Optional: Anmeldefrist könnte aus dem Markt kommen
        // Hier verwenden wir ein festes Datum als Beispiel
        // Sie können das anpassen, um das Datum aus dem Markt zu holen
        $anmeldefrist = now()->addDays(14)->format('d.m.Y');

        $result = $mailService->sendAnfrageWarteliste($anfrage, $anmeldefrist);

        if ($result) {
            Notification::make()
                ->title('Anfrage auf Warteliste gesetzt')
                ->body('Die Bestätigungs-E-Mail wurde versendet.')
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title('Fehler beim E-Mail-Versand')
                ->body('Die Anfrage wurde auf Warteliste gesetzt, aber die E-Mail konnte nicht versendet werden.')
                ->warning()
                ->send();
        }

        // Zurück zur Anfragen-Übersicht
        return redirect()->route('filament.admin.resources.anfrage.index');
    }

    /**
     * Anfrage absagen und E-Mail versenden
     */
    public function ausstellerAbsagen()
    {
        $a = $this->getCurrentAnfrage();

        try {
            // Warenangebot formatieren
            $warenangebotText = '';
            if (is_array($a->warenangebot)) {
                if (isset($a->warenangebot['subkategorien'])) {
                    $subkategorienIds = $a->warenangebot['subkategorien'];
                    $sonstiges = $a->warenangebot['sonstiges'] ?? null;
                    $namen = [];
                    if (!empty($subkategorienIds)) {
                        $namen = \App\Models\Subkategorie::whereIn('id', $subkategorienIds)->pluck('name')->toArray();
                        if ($sonstiges && in_array(24, $subkategorienIds)) {
                            $namen[] = "Sonstiges: " . $sonstiges;
                        }
                    } elseif ($sonstiges) {
                        $namen[] = "Sonstiges: " . $sonstiges;
                    }
                    $warenangebotText = implode(", ", $namen);
                }
            } else {
                $warenangebotText = $a->warenangebot ?? '';
            }

            // Aussteller-Objekt für MailService erstellen
            $aussteller = new Aussteller();
            $aussteller->email = $a->email;
            $aussteller->vorname = $a->vorname;
            $aussteller->name = $a->nachname;
            $aussteller->firma = $a->firma;
            $aussteller->warenangebot = $warenangebotText;

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

            // Anfrage Status auf abgesagt setzen
            $anfrageId = $a->id;
            $originalEmail = $a->email;
            $a->status = 'abgesagt';
            $a->save();

            $message = config('mail.dev_redirect_email')
                ? "Die Absage wurde im Testmodus an " . config('mail.dev_redirect_email') . " gesendet (Original: {$originalEmail}) und die Anfrage #{$anfrageId} wurde als abgesagt markiert."
                : "Die Absage wurde an {$originalEmail} gesendet und die Anfrage #{$anfrageId} wurde als abgesagt markiert.";

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
        // Sonstiges-Text aus Warenangebot extrahieren und zur bestehenden Aussteller-Bemerkung hinzufügen
        $bemerkung = $aussteller->bemerkung;  // Bestehende Bemerkung des Ausstellers behalten
        if (is_array($anfrage->warenangebot) && isset($anfrage->warenangebot['sonstiges'])) {
            $sonstigesText = "Sonstiges Warenangebot: " . $anfrage->warenangebot['sonstiges'];
            $bemerkung = $bemerkung ? $bemerkung . "\n\n" . $sonstigesText : $sonstigesText;
        }

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
            'mobil' => $anfrage->mobil,
            'email' => $anfrage->email,
            'bemerkung' => $bemerkung,  // Nur Sonstiges wird hinzugefügt, nicht die Anfrage-Bemerkung
            'steuer_id' => $anfrage->steuer_id,
            'handelsregisternummer' => $anfrage->handelsregisternummer,
            'stand' => $anfrage->stand,
            'warenangebot' => $anfrage->warenangebot,
            'vorfuehrung_am_stand' => $anfrage->vorfuehrung_am_stand,
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

        foreach ($anfrage->wuensche_zusatzleistungen as $leistungItem) {
            // Neues Format: Array mit leistung_id und menge
            $leistungId = $leistungItem['leistung_id'] ?? null;
            $menge = $leistungItem['menge'] ?? 1;
            
            if (!$leistungId) {
                Log::warning("Keine Leistungs-ID gefunden beim Import von Anfrage #{$anfrage->id}");
                continue;
            }
            
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
                'menge' => $menge, // Menge aus Anfrage übernehmen
                'sort' => $sortOrder++,
            ]);

            Log::info("Leistung '{$leistung->name}' (ID: {$leistung->id}) mit Menge {$menge} importiert für Buchung #{$buchung->id} aus Anfrage #{$anfrage->id}");
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

        // Neue Struktur: warenangebot hat 'subkategorien' Array
        $subkategorienIds = $anfrage->warenangebot['subkategorien'] ?? [];

        if (empty($subkategorienIds)) {
            return;
        }

        $validSubkategorieIds = [];

        foreach ($subkategorienIds as $subkategorieId) {
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
