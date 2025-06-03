<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('anfrage', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('markt_id');
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
            $table->json('stand');
            $table->json('warenangebot');
            $table->json('herkunft');
            $table->boolean('bereits_ausgestellt')->default(false);
            $table->boolean('importiert')->default(false);
            $table->text('bemerkung')->nullable();
            $table->timestamps();

            $table->foreign('markt_id')->references('id')->on('markt')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('anfrage');
    }
};
