<?php

namespace App\Http\Controllers;

use App\Models\Anfrage;
use App\Models\Markt;
use App\Models\Termin;
use App\Models\Medien;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Notifications\NeueAnfrageNotification;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class AnfrageController extends Controller
{
    public function create(Request $request)
    {
        $termine = Termin::with('markt')
            ->where('start', '>', now())
            ->orderBy('start')
            ->get();

        // Query Parameter für Vorauswahl eines Termins
        $selectedTerminId = null;
        if ($request->has('termin')) {
            $selectedTerminId = $request->get('termin');
        } elseif ($request->has('markt')) {
            // Legacy: Falls noch markt-Parameter verwendet wird
            $marktSlug = $request->get('markt');
            $markt = Markt::where('slug', $marktSlug)->first();
            if ($markt) {
                $naechsterTermin = $termine->where('markt_id', $markt->id)->first();
                $selectedTerminId = $naechsterTermin?->id;
            }
        }

        return view('anfrage.create', compact('termine', 'selectedTerminId'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'termin' => 'required|exists:termin,id',
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
            'stand' => 'required|array',
            // 'stand.art' => 'required|string|in:klein,mittel,groß',
            'stand.laenge' => 'nullable|numeric|min:0',
            'stand.tiefe' => 'nullable|numeric|min:0',
            'stand.flaeche' => 'nullable|numeric|min:0',
            'warenangebot' => 'required|array',
            'warenangebot.*' => 'string|in:kleidung,schmuck,kunst,accessoires,dekoration,lebensmittel,getraenke,handwerk,antiquitäten,sonstiges',
            'herkunft' => 'required|array',
            'herkunft.eigenfertigung' => 'required|integer|min:0|max:100',
            'herkunft.industrieware_nicht_entwicklungslaender' => 'required|integer|min:0|max:100',
            'herkunft.industrieware_entwicklungslaender' => 'required|integer|min:0|max:100',
            'bereits_ausgestellt' => 'nullable|boolean',
            'bemerkung' => 'nullable|string',
            // File Uploads
            'detailfotos_warenangebot' => 'nullable|array|max:4',
            'detailfotos_warenangebot.*' => 'image|mimes:jpeg,jpg,png,gif|max:5120', // 5MB
            'foto_verkaufsstand' => 'nullable|image|mimes:jpeg,jpg,png,gif|max:5120', // 5MB
            'foto_werkstatt' => 'nullable|image|mimes:jpeg,jpg,png,gif|max:5120', // 5MB
            'lebenslauf_vita' => 'nullable|file|mimes:pdf|max:10240', // 10MB
        ], [], [
            'herkunft.eigenfertigung' => 'Eigenfertigung',
            'herkunft.industrieware_nicht_entwicklungslaender' => 'Industrieware (nicht Entwicklungsland)',
            'herkunft.industrieware_entwicklungslaender' => 'Industrieware (Entwicklungsland)',
        ]);

        $anfrage = Anfrage::create([
            'termin_id' => $validated['termin'],
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
            'stand' => $validated['stand'],
            'warenangebot' => $validated['warenangebot'],
            'herkunft' => $validated['herkunft'],
            'bereits_ausgestellt' => $validated['bereits_ausgestellt'] ?? false,
            'importiert' => false,
            'bemerkung' => $validated['bemerkung'] ?? null,
        ]);

        // File Uploads in Medien-Tabelle speichern
        $sortOrder = 1;

        // Detailfotos Warenangebot (bis zu 4 Bilder)
        if ($request->hasFile('detailfotos_warenangebot')) {
            foreach ($request->file('detailfotos_warenangebot') as $file) {
                $filename = time() . '_detailfoto_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('anfragen/detailfotos', $filename, 'public');
                
                Medien::create([
                    'mediable_type' => Anfrage::class,
                    'mediable_id' => $anfrage->id,
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
                'mediable_id' => $anfrage->id,
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
                'mediable_id' => $anfrage->id,
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
                'mediable_id' => $anfrage->id,
                'category' => 'vita',
                'title' => 'Lebenslauf/Vita',
                'mime_type' => $file->getMimeType(),
                'file_extension' => $file->getClientOriginalExtension(),
                'path' => $path,
                'size' => $file->getSize(),
                'sort_order' => $sortOrder++,
            ]);
        }

        // Bestätigungsmail an den Anfragesteller über MailService
        try {
            $mailService = new \App\Services\MailService();
            $mailService->sendAnfrageBestaetigung($anfrage);
        } catch (\Exception $e) {
            Log::error('Fehler beim Versenden der Bestätigungsmail: ' . $e->getMessage());
        }

        // Benachrichtigung an alle User (sofort, nicht in Queue)
        User::all()->each(function ($user) use ($anfrage) {
            try {
                $user->notify(new NeueAnfrageNotification($anfrage));
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
}
