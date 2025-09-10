<?php

namespace App\Filament\Resources\EmailTemplateResource\Pages;

use App\Filament\Resources\EmailTemplateResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEmailTemplate extends EditRecord
{
    protected static string $resource = EmailTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('send_test')
                ->label('Test E-Mail senden')
                ->icon('heroicon-o-paper-airplane')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Test E-Mail senden')
                ->modalDescription(
                    fn() =>
                    "Möchten Sie eine Test-E-Mail für das Template '{$this->record->name}' an Ihre E-Mail-Adresse (" . auth()->user()->email . ") senden?"
                )
                ->action(function () {
                    $testEmail = auth()->user()->email;

                    try {
                        // Für rechnung_versand die echte RechnungMail-Klasse verwenden
                        if ($this->record->key === 'rechnung_versand') {
                            // Erste Rechnung für Test finden
                            $rechnung = \App\Models\Rechnung::first();
                            if (!$rechnung) {
                                throw new \Exception('Keine Test-Rechnung gefunden');
                            }

                            // MailService verwenden
                            $mailService = new \App\Services\MailService();
                            $success = $mailService->sendRechnung($rechnung);

                            if (!$success) {
                                throw new \Exception('E-Mail-Versand fehlgeschlagen');
                            }

                            \Filament\Notifications\Notification::make()
                                ->title('Test E-Mail versendet (Echte RechnungMail)')
                                ->body("Test E-Mail wurde erfolgreich an {$testEmail} gesendet. (Rechnung #{$rechnung->rechnungsnummer})")
                                ->success()
                                ->send();
                            return;
                        }

                        // Für andere Templates den bisherigen MailService verwenden
                        $mailService = new \App\Services\MailService();
                        $success = $mailService->send(
                            $this->record->key,
                            $testEmail,
                            [], // Leeres Array, da wir test=true verwenden
                            'Test Empfänger',
                            true // Test-Modus aktiviert - verwendet automatisch Dummy-Daten
                        );

                        if ($success) {
                            \Filament\Notifications\Notification::make()
                                ->title('Test E-Mail versendet')
                                ->body("Test E-Mail wurde erfolgreich an {$testEmail} gesendet.")
                                ->success()
                                ->send();
                        } else {
                            \Filament\Notifications\Notification::make()
                                ->title('Fehler beim Versenden')
                                ->body('Die Test E-Mail konnte nicht versendet werden.')
                                ->danger()
                                ->send();
                        }
                    } catch (\Exception $e) {
                        \Filament\Notifications\Notification::make()
                            ->title('Fehler')
                            ->body('Fehler beim Versenden: ' . $e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
            Actions\DeleteAction::make(),
        ];
    }
}
