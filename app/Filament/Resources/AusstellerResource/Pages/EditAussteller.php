<?php

namespace App\Filament\Resources\AusstellerResource\Pages;

use App\Filament\Resources\AusstellerResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAussteller extends EditRecord
{
    protected static string $resource = AusstellerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
