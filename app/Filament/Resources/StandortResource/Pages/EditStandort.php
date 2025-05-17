<?php

namespace App\Filament\Resources\StandortResource\Pages;

use App\Filament\Resources\StandortResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditStandort extends EditRecord
{
    protected static string $resource = StandortResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
