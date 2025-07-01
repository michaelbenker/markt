<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('rechnung', function (Blueprint $table) {
            $table->id();

            // Grunddaten
            $table->string('rechnungsnummer')->unique();
            $table->enum('status', ['draft', 'sent', 'paid', 'overdue', 'canceled', 'partial'])->default('draft');
            $table->unsignedBigInteger('buchung_id')->nullable(); // Optional - für manuelle Rechnungen
            $table->unsignedBigInteger('aussteller_id'); // für bessere Performance

            // Rechnungsdaten
            $table->date('rechnungsdatum');
            $table->date('lieferdatum')->nullable();
            $table->date('faelligkeitsdatum');
            $table->string('betreff');
            $table->text('anschreiben')->nullable();
            $table->text('schlussschreiben')->nullable();
            $table->string('zahlungsziel')->nullable(); // z.B. "14 Tage netto"
            $table->text('notiz')->nullable();

            // Finanzielle Daten (alle Beträge in Cent)
            $table->decimal('gesamtrabatt_prozent', 5, 2)->default(0);
            $table->bigInteger('gesamtrabatt_betrag')->default(0); // Cent
            $table->bigInteger('nettobetrag')->default(0); // Cent
            $table->bigInteger('steuerbetrag')->default(0); // Cent
            $table->bigInteger('bruttobetrag')->default(0); // Cent

            // Empfänger-Adresse (Snapshot zum Rechnungszeitpunkt)
            $table->string('empf_firma')->nullable();
            $table->string('empf_anrede')->nullable();
            $table->string('empf_vorname');
            $table->string('empf_name');
            $table->string('empf_strasse');
            $table->string('empf_hausnummer')->nullable();
            $table->string('empf_plz');
            $table->string('empf_ort');
            $table->string('empf_land');
            $table->string('empf_email');

            // Status-Tracking
            $table->timestamp('versendet_am')->nullable();
            $table->timestamp('bezahlt_am')->nullable();
            $table->bigInteger('bezahlter_betrag')->default(0); // Cent

            // ZUGFeRD (für später)
            $table->boolean('zugferd_enabled')->default(false);
            $table->text('zugferd_xml')->nullable();

            $table->timestamps();

            // Foreign Keys
            $table->foreign('buchung_id')->references('id')->on('buchung')->onDelete('set null');
            $table->foreign('aussteller_id')->references('id')->on('aussteller');

            // Indizes
            $table->index(['status', 'faelligkeitsdatum']);
            $table->index('rechnungsdatum');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rechnung');
    }
};
