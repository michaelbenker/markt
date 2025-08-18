<?php

namespace App\Http\Controllers;

use App\Models\Anfrage;
use App\Models\Markt;
use App\Models\Termin;
use App\Models\Medien;
use App\Models\Subkategorie;
use App\Models\Standort;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Notifications\NeueAnfrageNotification;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class AnfrageController extends Controller
{
    public function create(Request $request)
    {
        // Alle Märkte mit zukünftigen Terminen laden (= aktive Märkte)
        $aktiveMaerkte = Markt::with(['termine' => function($query) {
                $query->where('start', '>', now())
                      ->orderBy('start');
            }])
            ->get()
            ->filter(fn($markt) => $markt->termine->isNotEmpty());

        $selectedMarkt = null;
        $selectedTermine = collect();
        
        // 1. Markt per Query Parameter
        if ($request->has('markt')) {
            $marktSlug = $request->get('markt');
            $selectedMarkt = $aktiveMaerkte->firstWhere('slug', $marktSlug);
            
            if ($selectedMarkt) {
                $selectedTermine = $selectedMarkt->termine;
            }
        }
        
        // 2. Kein Markt per Query - prüfe ob nur ein aktiver Markt
        if (!$selectedMarkt && $aktiveMaerkte->count() === 1) {
            $selectedMarkt = $aktiveMaerkte->first();
            $selectedTermine = $selectedMarkt->termine;
        }
        
        // Subkategorien, Standorte und Leistungen für alle Märkte laden
        $subkategorienByMarkt = [];
        $standorteByMarkt = [];
        $leistungenByMarkt = [];
        
        foreach ($aktiveMaerkte as $markt) {
            // Subkategorien laden
            if ($markt->subkategorien) {
                $subkategorien = Subkategorie::whereIn('id', $markt->subkategorien)
                    ->with('kategorie')
                    ->orderBy('kategorie_id')
                    ->orderBy('name')
                    ->get();
                $subkategorienByMarkt[$markt->id] = $subkategorien;
            }
            
            // Standorte laden
            $standorte = $markt->standorte()->orderBy('name')->get();
            $standorteByMarkt[$markt->id] = $standorte;
            
            // Leistungen laden
            $markt->load('leistungen');
            $leistungenByMarkt[$markt->id] = $markt->leistungen;
        }

        return view('anfrage.create', compact(
            'aktiveMaerkte', 
            'selectedMarkt', 
            'selectedTermine',
            'subkategorienByMarkt', 
            'standorteByMarkt',
            'leistungenByMarkt'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'termine' => 'required|array|min:1',
            'termine.*' => 'required|exists:termin,id',
            'firma' => 'nullable|string|max:255',
            'anrede' => 'nullable|string|in:Herr,Frau,Divers',
            'vorname' => 'required|string|max:255',
            'nachname' => 'required|string|max:255',
            'strasse' => 'required|string|max:255',
            'hausnummer' => 'nullable|string|max:10',
            'plz' => 'required|string|max:10',
            'ort' => 'required|string|max:255',
            'land' => 'required|string|max:255',
            'telefon' => 'nullable|string|max:20',
            'email' => 'required|email|max:255',
            'steuer_id' => 'nullable|string|max:50',
            'handelsregisternummer' => 'nullable|string|max:50',
            'stand' => 'required|array',
            // 'stand.art' => 'required|string|in:klein,mittel,groß',
            'stand.laenge' => 'nullable|numeric|min:0',
            'stand.tiefe' => 'nullable|numeric|min:0',
            'stand.flaeche' => 'nullable|numeric|min:0',
            'stand.aufbau' => 'nullable|string|max:500',
            'wunsch_standort_id' => 'nullable|exists:standort,id',
            'warenangebot' => 'required|array',
            'warenangebot.*' => 'integer|exists:subkategorie,id',
            'warenangebot_sonstiges' => 'nullable|string|max:500',
            'herkunft' => 'required|array',
            'herkunft.eigenfertigung' => 'required|integer|min:0|max:100',
            'herkunft.industrieware' => 'required|integer|min:0|max:100',
            'bereits_ausgestellt' => 'nullable|string',
            'vorfuehrung_am_stand' => 'nullable|in:0,1',
            'bemerkung' => 'nullable|string',
            'soziale_medien' => 'nullable|array',
            'wuensche_zusatzleistungen' => 'nullable|array',
            'werbematerial' => 'nullable|array',
            'werbematerial.plakate_a3' => 'nullable|integer|min:0|max:100',
            'werbematerial.plakate_a1' => 'nullable|integer|min:0|max:100',
            'werbematerial.flyer' => 'nullable|integer|min:0|max:1000',
            'werbematerial.social_media_post' => 'nullable|boolean',
            // File Uploads
            'detailfotos_warenangebot' => 'nullable|array|max:4',
            'detailfotos_warenangebot.*' => 'image|mimes:jpeg,jpg,png,gif|max:5120', // 5MB
            'foto_verkaufsstand' => 'nullable|image|mimes:jpeg,jpg,png,gif|max:5120', // 5MB
            'foto_werkstatt' => 'nullable|image|mimes:jpeg,jpg,png,gif|max:5120', // 5MB
            'lebenslauf_vita' => 'nullable|file|mimes:pdf|max:10240', // 10MB
        ], [], [
            'herkunft.eigenfertigung' => 'Eigenfertigung',
            'herkunft.industrieware' => 'Industrieware',
        ]);

        // Warenangebot als strukturiertes JSON vorbereiten
        $warenangebotJson = [
            'subkategorien' => $validated['warenangebot'],
        ];
        
        // Sonstiges hinzufügen, wenn vorhanden
        if (!empty($validated['warenangebot_sonstiges']) && in_array(24, $validated['warenangebot'])) {
            $warenangebotJson['sonstiges'] = $validated['warenangebot_sonstiges'];
        }
        
        // Markt ID von den Terminen ermitteln
        $ersterTermin = Termin::find($validated['termine'][0]);
        $marktId = $ersterTermin->markt_id;
        
        // Eine Anfrage mit mehreren Terminen erstellen
        $anfrage = Anfrage::create([
            'markt_id' => $marktId,
            'termine' => $validated['termine'], // Array von Termin-IDs
            'firma' => $validated['firma'] ?? null,
            'anrede' => $validated['anrede'] ?? null,
            'vorname' => $validated['vorname'],
            'nachname' => $validated['nachname'],
            'strasse' => $validated['strasse'],
            'hausnummer' => $validated['hausnummer'] ?? null,
            'plz' => $validated['plz'],
            'ort' => $validated['ort'],
            'land' => $validated['land'],
            'telefon' => $validated['telefon'] ?? null,
            'email' => $validated['email'],
            'steuer_id' => $validated['steuer_id'] ?? null,
            'handelsregisternummer' => $validated['handelsregisternummer'] ?? null,
            'stand' => $validated['stand'],
            'warenangebot' => $warenangebotJson,
            'herkunft' => $validated['herkunft'],
            'bereits_ausgestellt' => $validated['bereits_ausgestellt'] ?? null,
            'vorfuehrung_am_stand' => (bool) ($validated['vorfuehrung_am_stand'] ?? false),
            'status' => 'offen',
            'bemerkung' => $validated['bemerkung'] ?? null,
            'soziale_medien' => $validated['soziale_medien'] ?? null,
            'wuensche_zusatzleistungen' => $validated['wuensche_zusatzleistungen'] ?? null,
            'werbematerial' => $this->transformWerbematerial($validated['werbematerial'] ?? []),
            'wunsch_standort_id' => $validated['wunsch_standort_id'] ?? null,
        ]);
        
        $ersteAnfrage = $anfrage;

        // File Uploads in Medien-Tabelle speichern (nur für erste Anfrage)
        $sortOrder = 1;

        // Detailfotos Warenangebot (bis zu 4 Bilder)
        if ($request->hasFile('detailfotos_warenangebot')) {
            foreach ($request->file('detailfotos_warenangebot') as $file) {
                $filename = time() . '_detailfoto_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('anfragen/detailfotos', $filename, 'public');

                Medien::create([
                    'mediable_type' => Anfrage::class,
                    'mediable_id' => $ersteAnfrage->id,
                    'category' => 'angebot',
                    'title' => 'Detailfoto Warenangebot',
                    'mime_type' => $file->getMimeType(),
                    'file_extension' => $file->getClientOriginalExtension(),
                    'path' => $path,
                    'size' => $file->getSize(),
                    'sort_order' => $sortOrder++,
                ]);
            }
        }

        // Foto Verkaufsstand
        if ($request->hasFile('foto_verkaufsstand')) {
            $file = $request->file('foto_verkaufsstand');
            $filename = time() . '_verkaufsstand_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('anfragen/verkaufsstand', $filename, 'public');

            Medien::create([
                'mediable_type' => Anfrage::class,
                'mediable_id' => $ersteAnfrage->id,
                'category' => 'stand',
                'title' => 'Foto Verkaufsstand',
                'mime_type' => $file->getMimeType(),
                'file_extension' => $file->getClientOriginalExtension(),
                'path' => $path,
                'size' => $file->getSize(),
                'sort_order' => $sortOrder++,
            ]);
        }

        // Foto Werkstatt
        if ($request->hasFile('foto_werkstatt')) {
            $file = $request->file('foto_werkstatt');
            $filename = time() . '_werkstatt_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('anfragen/werkstatt', $filename, 'public');

            Medien::create([
                'mediable_type' => Anfrage::class,
                'mediable_id' => $ersteAnfrage->id,
                'category' => 'werkstatt',
                'title' => 'Foto Werkstatt',
                'mime_type' => $file->getMimeType(),
                'file_extension' => $file->getClientOriginalExtension(),
                'path' => $path,
                'size' => $file->getSize(),
                'sort_order' => $sortOrder++,
            ]);
        }

        // Lebenslauf/Vita PDF
        if ($request->hasFile('lebenslauf_vita')) {
            $file = $request->file('lebenslauf_vita');
            $filename = time() . '_lebenslauf_' . uniqid() . '.pdf';
            $path = $file->storeAs('anfragen/lebenslaeufe', $filename, 'public');

            Medien::create([
                'mediable_type' => Anfrage::class,
                'mediable_id' => $ersteAnfrage->id,
                'category' => 'vita',
                'title' => 'Lebenslauf/Vita',
                'mime_type' => $file->getMimeType(),
                'file_extension' => $file->getClientOriginalExtension(),
                'path' => $path,
                'size' => $file->getSize(),
                'sort_order' => $sortOrder++,
            ]);
        }

        // Bestätigungsmail an den Anfragesteller über MailService (nur für erste Anfrage)
        try {
            $mailService = new \App\Services\MailService();
            $mailService->sendAnfrageBestaetigung($ersteAnfrage);
        } catch (\Exception $e) {
            Log::error('Fehler beim Versenden der Bestätigungsmail: ' . $e->getMessage());
        }

        // Benachrichtigung an alle User (sofort, nicht in Queue) - nur für erste Anfrage
        User::all()->each(function ($user) use ($ersteAnfrage) {
            try {
                $user->notify(new NeueAnfrageNotification($ersteAnfrage));
            } catch (\Exception $e) {
                Log::error('Fehler beim Versenden der Notification: ' . $e->getMessage());
            }
        });

        return redirect()->route('anfrage.success')->with('success', 'Ihre Anfrage wurde erfolgreich übermittelt.');
    }

    public function success()
    {
        return view('anfrage.success');
    }

    /**
     * Transformiert Werbematerial von Formular-Format zu strukturiertem Array
     */
    private function transformWerbematerial(array $werbematerial): array
    {
        $result = [];

        // Plakate A3
        if (!empty($werbematerial['plakate_a3']) && $werbematerial['plakate_a3'] > 0) {
            $result[] = [
                'typ' => 'plakat_a3',
                'anzahl' => (int) $werbematerial['plakate_a3'],
                'digital' => false,
                'physisch' => true,
            ];
        }

        // Plakate A1
        if (!empty($werbematerial['plakate_a1']) && $werbematerial['plakate_a1'] > 0) {
            $result[] = [
                'typ' => 'plakat_a1', 
                'anzahl' => (int) $werbematerial['plakate_a1'],
                'digital' => false,
                'physisch' => true,
            ];
        }

        // Flyer
        if (!empty($werbematerial['flyer']) && $werbematerial['flyer'] > 0) {
            $result[] = [
                'typ' => 'flyer',
                'anzahl' => (int) $werbematerial['flyer'],
                'digital' => false,
                'physisch' => true,
            ];
        }

        // Social Media Post
        if (!empty($werbematerial['social_media_post'])) {
            $result[] = [
                'typ' => 'social_media_post',
                'anzahl' => 1,
                'digital' => true,
                'physisch' => false,
            ];
        }

        return $result;
    }
}
