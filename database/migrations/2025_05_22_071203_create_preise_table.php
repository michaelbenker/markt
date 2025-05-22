<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('preis', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('kategorie');
            $table->string('bemerkung')->nullable();
            $table->string('einheit');
            $table->integer('preis');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('preis');
    }
};
