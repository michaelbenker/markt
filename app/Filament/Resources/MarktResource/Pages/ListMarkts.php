<?php

namespace App\Filament\Resources\MarktResource\Pages;

use App\Filament\Resources\MarktResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMarkts extends ListRecords
{
    protected static string $resource = MarktResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
