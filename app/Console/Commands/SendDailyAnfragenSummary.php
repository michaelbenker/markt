<?php

namespace App\Console\Commands;

use App\Mail\TaeglicheAnfragenUebersicht;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendDailyAnfragenSummary extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'anfragen:daily-summary {--test : Testmodus - sendet nur an ersten User}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sendet eine tägliche E-Mail-Zusammenfassung der neuen Anfragen an alle User';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Sende tägliche Anfragen-Übersicht...');

        // Alle aktiven User holen
        $users = User::all();

        if ($users->isEmpty()) {
            $this->warn('Keine User gefunden.');
            return;
        }

        $testMode = $this->option('test');

        if ($testMode) {
            $users = $users->take(1);
            $this->info('Testmodus: Sende nur an ersten User (' . $users->first()->email . ')');
        }

        $successCount = 0;
        $errorCount = 0;

        foreach ($users as $user) {
            try {
                Mail::to($user->email)->send(new TaeglicheAnfragenUebersicht());
                $this->info("✓ E-Mail gesendet an: {$user->email}");
                $successCount++;
            } catch (\Exception $e) {
                $this->error("✗ Fehler beim Senden an {$user->email}: " . $e->getMessage());
                $errorCount++;
            }
        }

        $this->info("Zusammenfassung: {$successCount} erfolgreich, {$errorCount} Fehler");

        if ($errorCount > 0) {
            return 1; // Exit code für Fehler
        }

        return 0;
    }
}
