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
        Schema::create('buchung_leistung', function (Blueprint $table) {
            $table->id();
            $table->foreignId('buchung_id')->constrained('buchung')->cascadeOnDelete();
            $table->foreignId('leistung_id')->constrained('leistung')->cascadeOnDelete();
            $table->decimal('preis', 8, 2)->nullable(); // optionaler Preis
            $table->integer('menge')->default(1);
            $table->integer('sort')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('buchung_leistung');
    }
};
