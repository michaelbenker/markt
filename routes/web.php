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

// Test-Route für E-Mail-Templates
Route::get('/test-email', function () {
    $aussteller = App\Models\Aussteller::first();
    if (!$aussteller) {
        return 'Kein Aussteller gefunden';
    }

    $service = new App\Services\EmailTemplateService();
    $data = [
        'aussteller_name' => $aussteller->name,
        'markt_name' => 'Test Markt',
        'stand_nummer' => '123',
        'anmeldedatum' => now()->format('d.m.Y')
    ];

    // Test Template Service
    $result = $service->renderTemplate('aussteller_bestaetigung', $data);

    if ($result['hasTemplate']) {
        return view('emails.template-wrapper', ['content' => $result['content']]);
    } else {
        return 'Kein Template verfügbar - würde Standard-View verwenden';
    }
});
