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

        // MailService verwenden
        $mailService = new \App\Services\MailService();
        $success = $mailService->sendRechnung($rechnung);

        return response()->json([
            'success' => $success,
            'message' => $success
                ? "✅ Test-Rechnung #{$rechnung->rechnungsnummer} versendet!"
                : 'Fehler beim Versand'
        ]);
    } catch (\Exception $e) {
        \Illuminate\Support\Facades\Log::error('Test-Mail Fehler: ' . $e->getMessage());
        return response()->json(['success' => false, 'message' => '❌ Fehler: ' . $e->getMessage()]);
    }
})->middleware('auth');
