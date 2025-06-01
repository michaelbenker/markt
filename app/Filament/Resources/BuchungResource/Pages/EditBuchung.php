<?php

namespace App\Filament\Resources\BuchungResource\Pages;

use App\Filament\Resources\BuchungResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBuchung extends EditRecord
{
    protected static string $resource = BuchungResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ActionGroup::make([
                Actions\DeleteAction::make(),
            ]),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return $data;
    }
}
