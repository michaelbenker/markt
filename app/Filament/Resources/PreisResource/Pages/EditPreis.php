<?php

namespace App\Filament\Resources\PreisResource\Pages;

use App\Filament\Resources\PreisResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPreis extends EditRecord
{
    protected static string $resource = PreisResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
