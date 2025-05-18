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
        Schema::create('standort', function (Blueprint $table) {
            $table->id();
            $table->foreignId('markt_id')->constrained('markt')->onDelete('cascade');
            $table->string('name');
            $table->text('beschreibung')->nullable();
            $table->string('flaeche')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('standort');
    }
};
