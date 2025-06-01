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
            Actions\Action::make('E-Mail senden')
                ->label('Bestätigung senden')
                ->action(function () {
                    \Illuminate\Support\Facades\Mail::send(
                        new \App\Mail\AusstellerBestaetigung($this->record->aussteller)
                    );
                })
                ->requiresConfirmation()
                ->color('success')
                ->icon('heroicon-o-envelope')
                ->iconSize('md'),
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
