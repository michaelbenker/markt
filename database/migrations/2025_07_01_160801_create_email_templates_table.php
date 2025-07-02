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
        Schema::create('email_templates', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique(); // z.B. 'aussteller_absage', 'rechnung_versand'
            $table->string('name'); // Anzeigename
            $table->string('subject'); // E-Mail-Betreff
            $table->longText('content'); // HTML-Inhalt
            $table->text('description')->nullable(); // Beschreibung des Templates
            $table->json('available_variables')->nullable(); // Verfügbare Platzhalter
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_templates');
    }
};
