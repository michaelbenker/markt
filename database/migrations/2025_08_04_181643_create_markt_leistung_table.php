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
        Schema::create('markt_leistung', function (Blueprint $table) {
            $table->id();
            $table->foreignId('markt_id')->constrained('markt')->onDelete('cascade');
            $table->foreignId('leistung_id')->constrained('leistung')->onDelete('cascade');
            $table->timestamps();
            
            $table->unique(['markt_id', 'leistung_id']);
        });
    }

    /**
     * Run the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('markt_leistung');
    }
};