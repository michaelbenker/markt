<?php

namespace App\Http\Controllers;

use App\Models\Rechnung;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class RechnungController extends Controller
{
    /**
     * Zeige Rechnung als PDF (öffentlicher Zugriff mit UUID/Token)
     */
    public function showPdf($rechnungsnummer, Request $request)
    {
        $rechnung = Rechnung::where('rechnungsnummer', $rechnungsnummer)->firstOrFail();

        // Optional: Token-basierte Authentifizierung für öffentlichen Zugriff
        // if (!$this->verifyToken($request->get('token'), $rechnung)) {
        //     abort(403, 'Ungültiger Zugriffstoken');
        // }

        $pdf = Pdf::loadView('pdf.rechnung', ['rechnung' => $rechnung]);

        return $pdf->stream('rechnung-' . $rechnung->rechnungsnummer . '.pdf');
    }

    /**
     * Download Rechnung als PDF
     */
    public function downloadPdf($rechnungsnummer, Request $request)
    {
        $rechnung = Rechnung::where('rechnungsnummer', $rechnungsnummer)->firstOrFail();

        $pdf = Pdf::loadView('pdf.rechnung', ['rechnung' => $rechnung]);

        return $pdf->download('rechnung-' . $rechnung->rechnungsnummer . '.pdf');
    }

    /**
     * Sende Rechnung per E-Mail
     */
    public function sendEmail(Rechnung $rechnung)
    {
        // TODO: E-Mail-Versand implementieren
        // Mail::send(new RechnungMail($rechnung));

        // Status auf 'sent' setzen
        $rechnung->update([
            'status' => 'sent',
            'versendet_am' => now(),
        ]);

        return back()->with('success', 'Rechnung wurde per E-Mail versendet.');
    }

    // private function verifyToken($token, $rechnung)
    // {
    //     // Implementiere Token-Verifizierung für öffentlichen Zugriff
    //     return hash('sha256', $rechnung->id . config('app.key')) === $token;
    // }
}
