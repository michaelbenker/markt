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
            $table->uuid('uuid')->unique();
            $table->enum('status', ['anfrage', 'bearbeitung', 'bestätigt', 'erledigt', 'abgelehnt'])->default('anfrage');
            $table->foreignId('markt_id')->constrained('markt')->cascadeOnDelete(); // Neu: Direkte Zuordnung zum Markt
            $table->json('termine')->nullable(); // Neu: Array von Termin-IDs
            $table->foreignId('standort_id')->constrained('standort')->cascadeOnDelete();
            $table->string('standplatz');
            $table->foreignId('aussteller_id')->constrained('aussteller')->cascadeOnDelete();
            $table->json('werbematerial')->nullable();
            $table->string('bemerkung')->nullable();
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
