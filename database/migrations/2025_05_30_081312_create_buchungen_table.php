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
        Schema::create('buchung', function (Blueprint $table) {
            $table->id();
            $table->enum('status', ['anfrage', 'bestätigt', 'abgelehnt'])->default('anfrage');
            $table->foreignId('termin_id')->constrained('termin')->cascadeOnDelete();
            $table->foreignId('standort_id')->constrained('standort')->cascadeOnDelete();
            $table->string('standplatz');
            $table->foreignId('aussteller_id')->constrained('aussteller')->cascadeOnDelete();
            $table->json('stand')->nullable();
            $table->json('warenangebot')->nullable();
            $table->json('herkunft')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('buchung');
    }
};
