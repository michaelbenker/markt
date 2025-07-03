<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BuchungPublicController;
use App\Http\Controllers\BuchungController;
use App\Http\Controllers\AnfrageController;
use App\Http\Controllers\RechnungController;

Route::get('/buchung/{uuid}', [BuchungPublicController::class, 'show']);

Route::get('/anfrage', [AnfrageController::class, 'create'])->name('anfrage.create');
Route::post('/anfrage', [AnfrageController::class, 'store'])->name('anfrage.store');
Route::get('/anfrage/success', [AnfrageController::class, 'success'])->name('anfrage.success');

// Rechnungsrouten (öffentlich mit Token)
Route::get('/rechnung/{rechnungsnummer}/pdf', [RechnungController::class, 'showPdf'])->name('rechnung.pdf');
Route::get('/rechnung/{rechnungsnummer}/download', [RechnungController::class, 'downloadPdf'])->name('rechnung.download');

Route::get('/', function () {
    return view('home');
});

// Test-Route für echte Mail-Klassen (nur für Admin)
Route::post('/admin/test-real-mail/rechnung', function () {
    try {
        // Erste Rechnung für Test finden
        $rechnung = \App\Models\Rechnung::first();
        if (!$rechnung) {
            return response()->json(['success' => false, 'message' => 'Keine Test-Rechnung gefunden']);
        }

        // RechnungMail-Klasse testen
        $mail = new \App\Mail\RechnungMail($rechnung);

        // Test-E-Mail an MAIL_DEV_REDIRECT_EMAIL senden
        $testEmail = config('mail.dev_redirect_email') ?: 'test@example.com';
        \Illuminate\Support\Facades\Mail::to($testEmail)->send($mail);

        return response()->json([
            'success' => true,
            'message' => "✅ Test-Mail erfolgreich an {$testEmail} gesendet! (Rechnung #{$rechnung->rechnungsnummer})"
        ]);
    } catch (\Exception $e) {
        \Illuminate\Support\Facades\Log::error('Test-Mail Fehler: ' . $e->getMessage());
        return response()->json(['success' => false, 'message' => '❌ Fehler: ' . $e->getMessage()]);
    }
})->middleware('auth');
