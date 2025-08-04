<?php

namespace App\Filament\Resources\TerminResource\Pages;

use App\Filament\Resources\TerminResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTermins extends ListRecords
{
    protected static string $resource = TerminResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
