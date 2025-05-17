<?php

namespace App\Filament\Resources\MarktResource\Pages;

use App\Filament\Resources\MarktResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMarkt extends EditRecord
{
    protected static string $resource = MarktResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
