<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BuchungPublicController;
use App\Http\Controllers\BuchungController;

Route::get('/buchung', [BuchungPublicController::class, 'create']);
Route::post('/buchung', [BuchungPublicController::class, 'store']);
Route::get('/buchung/{uuid}', [BuchungPublicController::class, 'show']);

Route::prefix('buchung')->name('buchung.')->group(function () {
    Route::get('/create', [BuchungController::class, 'create'])->name('create');
    Route::post('/', [BuchungController::class, 'store'])->name('store');
    Route::get('/success', [BuchungController::class, 'success'])->name('success');
});

Route::get('/', function () {
    return view('home');
});
