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
        // Prüfe ob es bereits Aussteller mit ID >= 10000 gibt
        $maxId = DB::table('aussteller')->max('id');
        
        // Setze AUTO_INCREMENT nur wenn die aktuelle max ID < 10000 ist
        if (!$maxId || $maxId < 10000) {
            DB::statement('ALTER TABLE aussteller AUTO_INCREMENT = 10000');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Rollback macht hier keinen Sinn, da wir AUTO_INCREMENT nicht zurücksetzen sollten
        // wenn bereits IDs >= 10000 vergeben wurden
    }
};