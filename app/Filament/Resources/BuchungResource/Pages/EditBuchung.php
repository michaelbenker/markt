<?php

namespace App\Filament\Resources\BuchungResource\Pages;

use App\Filament\Resources\BuchungResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\HtmlString;
use Filament\Actions\Action;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\BuchungProtokoll;
use Illuminate\Support\Facades\Auth;

class EditBuchung extends EditRecord
{
    protected static string $resource = BuchungResource::class;

    protected function getHeaderActions(): array
    {
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

            Action::make('E-Mail senden')
                ->label('Bestätigung senden')
                ->action(function () {
                    $mailService = new \App\Services\MailService();
                    $success = $mailService->sendAusstellerBestaetigung($this->record);

                    if ($success) {
                        \Filament\Notifications\Notification::make()
                            ->title('E-Mail erfolgreich versendet')
                            ->success()
                            ->send();
                    } else {
                        \Filament\Notifications\Notification::make()
                            ->title('Fehler beim E-Mail-Versand')
                            ->danger()
                            ->send();
                    }
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

            Action::make('absage')
                ->label('Absagen')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Buchung absagen')
                ->modalDescription(function () {
                    $aussteller = $this->record->aussteller;
                    $name = $aussteller->firma ?: "{$aussteller->vorname} {$aussteller->name}";
                    return new HtmlString("Sind Sie sicher, dass Sie die Buchung absagen und eine Absage-E-Mail an <strong>{$name}</strong> ({$aussteller->email}) senden möchten?");
                })
                ->modalSubmitActionLabel('Ja, absagen')
                ->modalCancelActionLabel('Abbrechen')
                ->action(function () {
                    // Status auf abgelehnt setzen
                    $this->record->update(['status' => 'abgelehnt']);

                    // Protokoll-Eintrag erstellen
                    BuchungProtokoll::create([
                        'buchung_id' => $this->record->id,
                        'user_id' => Auth::id(),
                        'aktion' => 'buchung_abgelehnt',
                        'from_status' => $this->record->getOriginal('status'),
                        'to_status' => 'abgelehnt',
                        'details' => 'Buchung wurde abgelehnt und Absage-E-Mail gesendet.',
                    ]);

                    // Absage-E-Mail senden - Anfrage aus Buchungsdaten erstellen
                    $anfrage = new \App\Models\Anfrage();
                    $anfrage->email = $this->record->aussteller->email;
                    $anfrage->markt = $this->record->termin->markt ?? null;
                    $anfrage->created_at = $this->record->created_at;
                    $anfrage->firma = $this->record->aussteller->firma;
                    $anfrage->warenangebot = $this->record->warenangebot;
                    $anfrage->vorname = $this->record->aussteller->vorname;
                    $anfrage->name = $this->record->aussteller->name;

                    $mailService = new \App\Services\MailService();
                    $success = $mailService->sendAusstellerAbsage($this->record->aussteller, [
                        'markt_name' => $this->record->termin->markt->name ?? 'Unbekannter Markt',
                        'eingereicht_am' => $this->record->created_at->format('d.m.Y')
                    ]);

                    if ($success) {
                        \Filament\Notifications\Notification::make()
                            ->title('Absage-E-Mail erfolgreich versendet')
                            ->success()
                            ->send();
                    } else {
                        \Filament\Notifications\Notification::make()
                            ->title('Fehler beim E-Mail-Versand')
                            ->danger()
                            ->send();
                    }

                    // Seite refreshen
                    $this->refreshFormData([$this->record->getKeyName()]);
                }),

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
