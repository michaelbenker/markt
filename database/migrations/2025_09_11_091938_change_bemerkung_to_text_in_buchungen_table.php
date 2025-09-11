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
        // Buchung Tabelle - Bemerkung auf text ändern
        Schema::table('buchung', function (Blueprint $table) {
            $table->text('bemerkung')->nullable()->change();
        });
        
        // Rechnung Tabelle - Betreff könnte länger werden
        Schema::table('rechnung', function (Blueprint $table) {
            $table->text('betreff')->change();
        });
        
        // Email Templates - Subject könnte länger werden
        Schema::table('email_templates', function (Blueprint $table) {
            $table->text('subject')->change();
        });
        
        // Aussteller - Homepage URLs können sehr lang werden
        Schema::table('aussteller', function (Blueprint $table) {
            $table->text('homepage')->nullable()->change();
            $table->text('briefanrede')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('buchung', function (Blueprint $table) {
            $table->string('bemerkung')->nullable()->change();
        });
        
        Schema::table('rechnung', function (Blueprint $table) {
            $table->string('betreff')->change();
        });
        
        Schema::table('email_templates', function (Blueprint $table) {
            $table->string('subject')->change();
        });
        
        Schema::table('aussteller', function (Blueprint $table) {
            $table->string('homepage')->nullable()->change();
            $table->string('briefanrede')->nullable()->change();
        });
    }
};
