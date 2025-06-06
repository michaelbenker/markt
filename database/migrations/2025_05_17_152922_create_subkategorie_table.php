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
        Schema::create('subkategorie', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kategorie_id')->constrained('kategorie')->onDelete('cascade');
            $table->string('name');
            $table->text('bemerkung')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subkategorie');
    }
};
