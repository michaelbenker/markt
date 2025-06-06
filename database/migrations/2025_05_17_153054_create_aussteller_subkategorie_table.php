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
        Schema::create('aussteller_subkategorie', function (Blueprint $table) {
            $table->foreignId('aussteller_id')->constrained('aussteller')->onDelete('cascade');
            $table->foreignId('subkategorie_id')->constrained('subkategorie')->onDelete('cascade');
            $table->primary(['aussteller_id', 'subkategorie_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aussteller_subkategorie');
    }
};
