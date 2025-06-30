<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('rechnung_position', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('rechnung_id');
            $table->unsignedBigInteger('buchung_leistung_id')->nullable(); // Referenz zur ursprünglichen Buchungsleistung

            // Position (Snapshot zum Rechnungszeitpunkt)
            $table->integer('position')->default(1);
            $table->string('bezeichnung');
            $table->text('beschreibung')->nullable();
            $table->decimal('menge', 8, 2);
            $table->string('einheit')->default('Stück');
            $table->bigInteger('einzelpreis'); // Cent
            $table->decimal('rabatt_prozent', 5, 2)->default(0);
            $table->bigInteger('nettobetrag'); // Cent
            $table->decimal('steuersatz', 5, 2)->default(19.00); // MwSt-Satz
            $table->bigInteger('steuerbetrag'); // Cent
            $table->bigInteger('bruttobetrag'); // Cent

            $table->timestamps();

            // Foreign Keys
            $table->foreign('rechnung_id')->references('id')->on('rechnung')->onDelete('cascade');
            $table->foreign('buchung_leistung_id')->references('id')->on('buchung_leistung')->onDelete('set null');

            // Index für Sortierung
            $table->index(['rechnung_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rechnung_position');
    }
};
