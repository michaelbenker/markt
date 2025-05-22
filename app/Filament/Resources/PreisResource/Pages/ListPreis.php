<?php

namespace App\Filament\Resources\PreisResource\Pages;

use App\Filament\Resources\PreisResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPreis extends ListRecords
{
    protected static string $resource = PreisResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
