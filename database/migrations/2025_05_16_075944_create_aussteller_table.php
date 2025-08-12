<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('aussteller', function (Blueprint $table) {
            $table->id();
            $table->string('firma')->nullable();
            $table->string('anrede')->nullable();
            $table->string('vorname')->nullable();
            $table->string('name')->nullable();
            $table->string('strasse')->nullable();
            $table->string('hausnummer')->nullable();
            $table->string('plz')->nullable();
            $table->string('ort')->nullable();
            $table->string('land')->default('Deutschland');
            $table->string('telefon')->nullable();
            $table->string('mobil')->nullable();
            $table->string('homepage')->nullable();
            $table->string('email')->unique();
            $table->string('briefanrede')->nullable();
            $table->text('bemerkung')->nullable();
            $table->boolean('vorfuehrung_am_stand')->default(false);
            $table->string('steuer_id')->nullable();
            $table->string('handelsregisternummer')->nullable();
            $table->json('herkunft')->nullable();
            $table->integer('rating')->default(0);
            $table->text('rating_bemerkung')->nullable();
            $table->json('soziale_medien')->nullable();
            $table->json('bilder')->nullable();
            $table->json('files')->nullable();
            $table->json('stand')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aussteller');
    }
};
