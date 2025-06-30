<?php

namespace App\Filament\Resources\RechnungResource\Pages;

use App\Filament\Resources\RechnungResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRechnungs extends ListRecords
{
    protected static string $resource = RechnungResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Neue Rechnung'),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            // TODO: Rechnung-Widgets (Statistiken)
        ];
    }
}
