<?php

namespace App\Filament\Resources\BuchungResource\Pages;

use App\Filament\Resources\BuchungResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\HtmlString;
use Filament\Actions\Action;
use Barryvdh\DomPDF\Facade\Pdf;

class EditBuchung extends EditRecord
{
    protected static string $resource = BuchungResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('print')
                ->label('Buchung drucken')
                ->icon('heroicon-o-printer')
                ->action(function ($record) {
                    $pdf = Pdf::loadView('pdf.buchung', ['buchung' => $record]);
                    return response()->streamDownload(function () use ($pdf) {
                        echo $pdf->output();
                    }, 'buchung-' . $record->id . '.pdf');
                }),
            Actions\Action::make('E-Mail senden')
                ->label('Bestätigung senden')
                ->action(function () {
                    \Illuminate\Support\Facades\Mail::send(
                        new \App\Mail\AusstellerBestaetigung($this->record->aussteller)
                    );
                })
                ->requiresConfirmation()
                ->modalHeading('E-Mail senden')
                ->modalDescription(function () {
                    $aussteller = $this->record->aussteller;
                    $name = $aussteller->firma ?: "{$aussteller->vorname} {$aussteller->name}";
                    return new HtmlString("Sind Sie sicher, dass Sie eine Nachricht mit der Terminbestätigung an den Aussteller <strong>{$name}</strong> ({$aussteller->email}) schicken möchten?");
                })
                ->modalSubmitActionLabel('Ja, E-Mail senden')
                ->modalCancelActionLabel('Abbrechen')
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
