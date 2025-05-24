<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('termin', function (Blueprint $table) {
            $table->id();
            $table->foreignId('markt_id')->constrained('markt')->onDelete('cascade');
            $table->date('start');
            $table->date('ende');
            $table->string('bemerkung')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('termin');
    }
};
