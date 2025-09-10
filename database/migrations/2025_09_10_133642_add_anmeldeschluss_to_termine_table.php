<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('termin', function (Blueprint $table) {
            $table->date('anmeldeschluss')->after('ende')->nullable();
        });
        
        // Setze Standardwerte für existierende Einträge (30 Tage vor Start)
        DB::table('termin')->whereNull('anmeldeschluss')->update([
            'anmeldeschluss' => DB::raw("DATE_SUB(start, INTERVAL 30 DAY)")
        ]);
        
        // Mache das Feld zur Pflicht
        Schema::table('termin', function (Blueprint $table) {
            $table->date('anmeldeschluss')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('termin', function (Blueprint $table) {
            $table->dropColumn('anmeldeschluss');
        });
    }
};
