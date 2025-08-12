<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aussteller_tag', function (Blueprint $table) {
            $table->id();
            $table->foreignId('aussteller_id')->constrained('aussteller')->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained('tags')->cascadeOnDelete();
            $table->text('notiz')->nullable(); // Optionale Notiz für den Tag
            $table->timestamps();
            
            // Unique constraint um doppelte Tags zu verhindern
            $table->unique(['aussteller_id', 'tag_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aussteller_tag');
    }
};