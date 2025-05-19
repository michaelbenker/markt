<?php

namespace App\Filament\Resources\AusstellerResource\Pages;

use App\Filament\Resources\AusstellerResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAussteller extends ListRecords
{
    protected static string $resource = AusstellerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
