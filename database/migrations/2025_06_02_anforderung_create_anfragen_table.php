<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('anfrage', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('termin_id');
            $table->string('firma')->nullable();
            $table->string('anrede')->nullable();
            $table->string('vorname');
            $table->string('nachname');
            $table->string('strasse');
            $table->string('hausnummer')->nullable();
            $table->string('plz');
            $table->string('ort');
            $table->string('land');
            $table->string('telefon')->nullable();
            $table->string('email');
            $table->string('steuer_id')->nullable();
            $table->string('handelsregisternummer')->nullable();
            $table->json('stand');
            $table->unsignedBigInteger('wunsch_standort_id')->nullable();
            $table->json('warenangebot');
            $table->json('herkunft');
            $table->text('bereits_ausgestellt')->nullable();
            $table->boolean('vorfuehrung_am_stand')->default(false);
            $table->boolean('importiert')->default(false);
            $table->text('bemerkung')->nullable();
            $table->json('soziale_medien')->nullable();
            $table->json('wuensche_zusatzleistungen')->nullable();
            $table->json('werbematerial')->nullable();
            $table->timestamps();

            $table->foreign('termin_id')->references('id')->on('termin')->onDelete('cascade');
            $table->foreign('wunsch_standort_id')->references('id')->on('standort')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('anfrage');
    }
};
