<?php

namespace App\Filament\Resources\TerminResource\Pages;

use App\Filament\Resources\TerminResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTermin extends EditRecord
{
    protected static string $resource = TerminResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
