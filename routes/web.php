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
