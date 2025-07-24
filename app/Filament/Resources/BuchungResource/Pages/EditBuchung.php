<?php

namespace App\Filament\Resources\BuchungResource\Pages;

use App\Filament\Resources\BuchungResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions\Action;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\BuchungProtokoll;
use Illuminate\Support\Facades\Auth;
use App\Filament\Actions\EmailSendAction;
use App\Filament\Actions\EmailAbsageAction;

class EditBuchung extends EditRecord
{
    protected static string $resource = BuchungResource::class;

    protected function getHeaderActions(): array
    {
        // Keine Aktionen für abgelehnte Buchungen
        if ($this->record->status === 'abgelehnt') {
            return [];
        }
        
        return [
            Action::make('create_rechnung')
                ->label('Rechnung erstellen')
                ->icon('heroicon-o-document-text')
                ->color('success')
                ->disabled(fn($record) => $record->hatAktiveRechnung() || !$record->aussteller_id || !$record->leistungen()->exists())
                ->action(function ($record) {
                    // Erstelle Rechnung aus Buchung
                    $rechnung = \App\Services\RechnungService::createFromBuchung($record);

                    return redirect()->route('filament.admin.resources.rechnungen.edit', $rechnung);
                }),

            Action::make('view_rechnungen')
                ->label('Rechnungen anzeigen')
                ->icon('heroicon-o-eye')
                ->visible(fn($record) => $record->hatRechnungen())
                ->url(fn($record) => route('filament.admin.resources.rechnungen.index', [
                    'tableFilters[buchung_typ][value]' => 'buchung',
                    'tableSearch' => $record->id,
                ])),

            Action::make('print')
                ->label('Buchung drucken')
                ->icon('heroicon-o-printer')
                ->action(function ($record) {
                    $pdf = Pdf::loadView('pdf.buchung', ['buchung' => $record]);
                    BuchungProtokoll::create([
                        'buchung_id' => $record->id,
                        'user_id' => Auth::id(),
                        'aktion' => 'buchungsbestaetigung_pdf_erzeugt',
                        'from_status' => $record->status,
                        'to_status' => $record->status,
                        'details' => 'Anmeldebestätigung als PDF erzeugt.',
                    ]);
                    return response()->streamDownload(function () use ($pdf) {
                        echo $pdf->output();
                    }, 'buchung-' . $record->id . '.pdf');
                }),

            EmailSendAction::make('send_email')
                ->label('Bestätigung senden'),

            EmailAbsageAction::make('send_absage_email'),

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
    
    protected function authorizeAccess(): void
    {
        // Prüfe ob die Buchung bearbeitet werden kann
        if ($this->record->status === 'abgelehnt') {
            // Erlauben des Zugriffs zum Anzeigen, aber weitere Berechtigungen werden durch andere Methoden gesteuert
        }
        
        parent::authorizeAccess();
    }
    
    protected function hasFormActionsContainer(): bool
    {
        // Keine Form-Actions (Save, Cancel) für abgelehnte Buchungen
        return $this->record->status !== 'abgelehnt';
    }
}
