<?php

namespace App\Filament\Resources\StandortResource\Pages;

use App\Filament\Resources\StandortResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListStandort extends ListRecords
{
    protected static string $resource = StandortResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
