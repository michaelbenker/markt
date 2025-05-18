<?php

namespace App\Filament\Resources\HauptkategorieResource\Pages;

use App\Filament\Resources\HauptkategorieResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditHauptkategorie extends EditRecord
{
    protected static string $resource = HauptkategorieResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
