<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('color')->default('#6b7280'); // Standardfarbe grau
            $table->enum('type', ['positiv', 'neutral', 'negativ'])->default('neutral');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tags');
    }
};