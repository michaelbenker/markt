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
        Schema::create('buchung_protokoll', function (Blueprint $table) {
            $table->id();
            $table->foreignId('buchung_id')->constrained('buchung')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('aktion');
            $table->string('from_status')->default('');
            $table->string('to_status')->default('');
            $table->text('details')->nullable();
            $table->json('daten')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('buchung_protokoll');
    }
};
