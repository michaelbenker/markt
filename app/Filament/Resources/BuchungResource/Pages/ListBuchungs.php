<?php

namespace App\Filament\Resources\BuchungResource\Pages;

use App\Filament\Resources\BuchungResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBuchungs extends ListRecords
{
    protected static string $resource = BuchungResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
