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
        Schema::create('medien', function (Blueprint $table) {
            $table->id();
            $table->morphs('mediable'); // mediable_type, mediable_id für Polymorphic Relation
            $table->enum('category', ['angebot', 'stand', 'werkstatt', 'vita'])->nullable();
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->string('mime_type')->nullable();
            $table->string('file_extension', 10)->nullable();
            $table->string('path');
            $table->unsignedBigInteger('size')->nullable(); // Dateigröße in Bytes
            $table->json('metadata')->nullable(); // Für zusätzliche Informationen (Bildbreite, etc.)
            $table->integer('sort_order')->default(0); // Für Sortierung
            $table->timestamps();
            
            $table->index(['category']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medien');
    }
};
