<?php

namespace App\Filament\Resources\AnfrageResource\Pages;

use App\Filament\Resources\AnfrageResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAnfrage extends ListRecords
{
    protected static string $resource = AnfrageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Kein Create-Button mehr
        ];
    }
}
