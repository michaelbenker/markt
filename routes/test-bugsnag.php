<?php

use Illuminate\Support\Facades\Route;

// BugSnag Test Route (nur für Development)
if (app()->environment('local')) {
    Route::get('/test-bugsnag', function () {
        // Explizit BugSnag aufrufen vor dem Werfen
        if (app()->bound('bugsnag')) {
            app('bugsnag')->notifyException(new \Exception('Manueller Test vor throw'));
        }
        
        // Test 1: Einfacher Fehler
        throw new \Exception('Test-Fehler von BugSnag Integration');
    });
    
    Route::get('/test-bugsnag-context', function () {
        // Test 2: Fehler mit Kontext
        if (app()->bound('bugsnag')) {
            app('bugsnag')->leaveBreadcrumb('Test Breadcrumb', 'process');
            app('bugsnag')->setUser([
                'id' => auth()->id() ?? 'guest',
                'name' => auth()->user()?->name ?? 'Guest User',
                'email' => auth()->user()?->email ?? 'guest@example.com',
            ]);
        }
        
        throw new \RuntimeException('Test-Fehler mit Benutzer-Kontext');
    });
}