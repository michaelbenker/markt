<?php

namespace App\Filament\Resources\LeistungResource\Pages;

use App\Filament\Resources\LeistungResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLeistung extends ListRecords
{
    protected static string $resource = LeistungResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
