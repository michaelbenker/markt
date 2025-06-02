<?php

namespace App\Http\Controllers;

use App\Models\Buchung;
use App\Models\Markt;
use App\Models\Aussteller;
use App\Services\BuchungService;
use Illuminate\Http\Request;

class BuchungController extends Controller
{
    protected $buchungService;

    public function __construct(BuchungService $buchungService)
    {
        $this->buchungService = $buchungService;
    }

    public function create()
    {
        $maerkte = Markt::where('status', 'aktiv')->get();
        return view('buchung.create', compact('maerkte'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'markt' => 'required|exists:markt,id',
            'firma' => 'nullable|string|max:255',
            'anrede' => 'nullable|string|in:Herr,Frau,Divers',
            'vorname' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'strasse' => 'required|string|max:255',
            'hausnummer' => 'nullable|string|max:10',
            'plz' => 'required|string|max:10',
            'ort' => 'required|string|max:255',
            'land' => 'required|string|max:255',
            'telefon' => 'nullable|string|max:20',
            'mobil' => 'nullable|string|max:20',
            'email' => 'required|email|max:255',
            'homepage' => 'nullable|url|max:255',
            'bemerkung' => 'nullable|string',
            'stand' => 'required|array',
            'stand.art' => 'required|string|in:klein,mittel,groß',
            'stand.laenge' => 'nullable|numeric|min:0',
            'stand.flaeche' => 'nullable|numeric|min:0',
            'warenangebot' => 'required|array',
            'warenangebot.*' => 'string|in:kleidung,schmuck,kunst,accessoires,dekoration,lebensmittel,getraenke,handwerk,antiquitäten,sonstiges',
            'herkunft' => 'required|array',
            'herkunft.eigenfertigung' => 'required|integer|min:0|max:100',
            'herkunft.industrieware_nicht_entwicklungslaender' => 'required|integer|min:0|max:100',
            'herkunft.industrieware_entwicklungslaender' => 'required|integer|min:0|max:100',
        ]);

        try {
            $buchung = $this->buchungService->createBuchung($validated);
            return redirect()->route('buchung.success')->with('success', 'Ihre Buchungsanfrage wurde erfolgreich übermittelt.');
        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['error' => 'Es ist ein Fehler aufgetreten. Bitte versuchen Sie es später erneut.']);
        }
    }

    public function success()
    {
        return view('buchung.success');
    }
}
