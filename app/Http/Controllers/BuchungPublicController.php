<?php

namespace App\Http\Controllers;

use App\Models\Buchung;

class BuchungPublicController extends Controller
{
    public function show(string $uuid)
    {
        $buchung = Buchung::where('uuid', $uuid)->firstOrFail();

        return view('buchung.show', compact('buchung'));
    }
}
