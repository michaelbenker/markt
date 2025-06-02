<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Buchung;

class BuchungPublicController extends Controller
{
    public function create()
    {
        return view('buchung.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'aussteller_name' => 'required|string|max:255',
            'email' => 'required|email',
            'warenangebot' => 'nullable|string',
            // weitere Felder
        ]);

        $buchung = Buchung::create([
            'status' => 'anfrage',
            'uuid' => \Str::uuid(),
            'warenangebot' => $validated['warenangebot'],
            // etc.
        ]);

        // optional: Bestätigungsmail senden

        return redirect("/buchung/{$buchung->uuid}");
    }

    public function show(string $uuid)
    {
        $buchung = Buchung::where('uuid', $uuid)->firstOrFail();

        return view('buchung.show', compact('buchung'));
    }
}
