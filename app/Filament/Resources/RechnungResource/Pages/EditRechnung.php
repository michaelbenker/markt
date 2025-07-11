<?php

namespace App\Filament\Resources\RechnungResource\Pages;

use App\Filament\Resources\RechnungResource;
use App\Models\Rechnung;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class EditRechnung extends EditRecord
{
    protected static string $resource = RechnungResource::class;

    protected function getHeaderActions(): array
    {
        $actions = [];

        // PDF-Download
        $actions[] = Actions\Action::make('pdf')
            ->label('PDF herunterladen')
            ->icon('heroicon-o-document-arrow-down')
            ->action(function () {
                $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.rechnung', ['rechnung' => $this->record]);

                return response()->streamDownload(function () use ($pdf) {
                    echo $pdf->output();
                }, 'rechnung-' . $this->record->rechnungsnummer . '.pdf');
            });

        // E-Mail senden (nur bei Draft)
        if (in_array($this->record->status, ['draft', 'sent', 'partial', 'overdue'])) {
            $actions[] = Actions\Action::make('send_email')
                ->label('E-Mail senden')
                ->icon('heroicon-o-envelope')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Rechnung per E-Mail senden')
                ->modalDescription(function () {
                    $empfaenger = $this->record->empf_email;
                    return "Möchten Sie die Rechnung #{$this->record->rechnungsnummer} an {$empfaenger} senden?";
                })->action(function () {
                    // E-Mail-Adresse bestimmen
                    $toEmail = config('mail.dev_redirect_email')
                        ? config('mail.dev_redirect_email')
                        : $this->record->empf_email;

                    // E-Mail über den zentralen MailService senden
                    $mailService = new \App\Services\MailService();
                    $success = $mailService->send(
                        'rechnung_versand',
                        $toEmail,
                        [
                            'rechnung' => $this->record,
                            'aussteller' => $this->record->aussteller,
                        ],
                        $this->record->empf_vorname . ' ' . $this->record->empf_name
                    );

                    if ($success) {
                        // Status auf "sent" setzen
                        $this->record->update([
                            'status' => 'sent',
                            'versendet_am' => now(),
                        ]);

                        Notification::make()
                            ->title('Rechnung wurde versendet')
                            ->success()
                            ->send();

                        // Seite neu laden, damit Status sofort sichtbar ist
                        $this->redirect(request()->header('Referer') ?? url()->current());
                    } else {
                        Notification::make()
                            ->title('Fehler beim Versenden')
                            ->body('Die Rechnung konnte nicht versendet werden.')
                            ->danger()
                            ->send();
                    }

                    // Livewire-kompatible Lösung
                    $this->record = $this->record->fresh();
                });
        }

        // Als bezahlt markieren
        if (in_array($this->record->status, ['sent', 'partial', 'overdue'])) {
            $actions[] = Actions\Action::make('mark_paid')
                ->label('Als bezahlt markieren')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->form([
                    \Filament\Forms\Components\DatePicker::make('bezahlt_am')
                        ->label('Bezahlt am')
                        ->default(now())
                        ->required(),

                    \Filament\Forms\Components\TextInput::make('bezahlter_betrag')
                        ->label('Bezahlter Betrag €')
                        ->numeric()
                        ->step(0.01)
                        ->default($this->record->bruttobetrag / 100) // Cent zu Euro
                        ->required(),
                ])
                ->action(function (array $data) {
                    $bezahlterBetragCent = round($data['bezahlter_betrag'] * 100); // Euro zu Cent
                    $status = $bezahlterBetragCent >= $this->record->bruttobetrag ? 'paid' : 'partial';

                    $this->record->update([
                        'status' => $status,
                        'bezahlt_am' => $data['bezahlt_am'],
                        'bezahlter_betrag' => $bezahlterBetragCent, // In Cent speichern
                    ]);

                    Notification::make()
                        ->title('Zahlung wurde erfasst')
                        ->success()
                        ->send();

                    // Livewire-kompatible Lösung
                    $this->record = $this->record->fresh();
                });
        }

        if ($this->record->status !== 'canceled') {
            $actions[] = Actions\Action::make('storno')
                ->label('Stornieren')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Stornieren')
                ->modalDescription('Möchten Sie diese Rechnung wirklich stornieren?')
                ->modalSubmitActionLabel('Ja, stornieren')
                ->modalCancelActionLabel('Abbrechen')
                ->action(function () {
                    try {
                        $this->record->update(['status' => 'canceled']);
                        \Filament\Notifications\Notification::make()
                            ->title('Rechnung wurde storniert')
                            ->success()
                            ->send();

                        // Einfache Lösung: Seite über JavaScript neu laden
                        $this->js('window.location.reload();');
                    } catch (\Exception $e) {
                        Log::error('Fehler beim Stornieren: ' . $e->getMessage());

                        \Filament\Notifications\Notification::make()
                            ->title('Fehler: ' . $e->getMessage())
                            ->danger()
                            ->send();
                    }
                });
        }

        // Kopieren und Stornieren
        if (in_array($this->record->status, ['draft', 'sent'])) {
            $actions[] = Actions\Action::make('kopieren_und_stornieren')
                ->label('Kopieren und Stornieren')
                ->icon('heroicon-o-document-duplicate')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Rechnung kopieren und stornieren')
                ->modalDescription('Eine neue Rechnung wird als Entwurf erstellt und diese Rechnung wird storniert. Möchten Sie fortfahren?')
                ->modalSubmitActionLabel('Ja, kopieren und stornieren')
                ->modalCancelActionLabel('Abbrechen')
                ->action(function () {
                    try {
                        Log::info('Kopieren und Stornieren wird ausgeführt für Rechnung: ' . $this->record->rechnungsnummer);

                        // Rechnung komplett kopieren
                        $neueRechnung = $this->record->replicate();
                        $neueRechnung->notiz = 'Kopie von ' . $this->record->rechnungsnummer;

                        // Neue Rechnungsnummer generieren
                        // Fortlaufende Rechnungsnummer generieren
                        $maxNummer = \App\Models\Rechnung::max('rechnungsnummer');
                        $neueNummer = is_numeric($maxNummer) ? ((int)$maxNummer + 1) : 1001;
                        $neueRechnung->rechnungsnummer = (string)$neueNummer;
                        $neueRechnung->status = 'draft';
                        $neueRechnung->versendet_am = null;
                        $neueRechnung->bezahlt_am = null;
                        $neueRechnung->bezahlter_betrag = 0;
                        $neueRechnung->zugferd_xml = null;
                        $neueRechnung->created_at = now();
                        $neueRechnung->updated_at = now();

                        $neueRechnung->save();

                        // Alle Positionen kopieren
                        foreach ($this->record->positionen as $position) {
                            $neuePosition = $position->replicate();
                            $neuePosition->rechnung_id = $neueRechnung->id;
                            $neuePosition->created_at = now();
                            $neuePosition->updated_at = now();
                            $neuePosition->save();
                        }

                        // Beträge neu berechnen für die neue Rechnung
                        $neueRechnung->calculateTotals();
                        $neueRechnung->save();

                        // Ursprüngliche Rechnung stornieren
                        $this->record->update(['status' => 'canceled']);

                        Notification::make()
                            ->title('Rechnung wurde kopiert und storniert')
                            ->body('Neue Rechnung: ' . $neueRechnung->rechnungsnummer)
                            ->success()
                            ->send();

                        // Zur neuen Rechnung weiterleiten
                        $this->js('window.location.href = "/admin/rechnungen/' . $neueRechnung->id . '/edit";');
                    } catch (\Exception $e) {
                        Log::error('Fehler beim Kopieren und Stornieren: ' . $e->getMessage());

                        Notification::make()
                            ->title('Fehler: ' . $e->getMessage())
                            ->danger()
                            ->send();
                    }
                });
        }

        // Löschen (nur bei Draft)
        if ($this->record->status === 'draft') {
            $actions[] = Actions\ActionGroup::make([
                Actions\DeleteAction::make(),
            ]);
        }

        // Debug: Schauen ob Actions da sind
        // dd('Actions count:', count($actions), 'Record status:', $this->record->status);

        return $actions;
    }

    public function cancelRechnung()
    {
        try {
            $this->record->update(['status' => 'canceled']);

            Notification::make()
                ->title('Rechnung wurde storniert')
                ->success()
                ->send();

            // Seite neu laden
            return redirect()->to(request()->url());
        } catch (\Exception $e) {
            Notification::make()
                ->title('Fehler beim Stornieren')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Verhindere Änderungen an nicht editierbaren Rechnungen
        if (!$this->record->isEditable()) {
            Notification::make()
                ->title('Rechnung ist nicht mehr editierbar')
                ->warning()
                ->send();

            // Gib die ursprünglichen Daten zurück
            return $this->record->toArray();
        }

        return $data;
    }

    protected function afterSave(): void
    {
        // Beträge neu berechnen nach dem Speichern
        if ($this->record->isEditable()) {
            // Positionen neu nummerieren
            $this->record->positionen()
                ->orderBy('id')
                ->get()
                ->each(function ($position, $index) {
                    $position->update(['position' => $index + 1]);
                });

            $this->record->calculateTotals();
            $this->record->save();
        }
    }

    protected function getFormActions(): array
    {
        $actions = parent::getFormActions();

        // Entferne Save-Button wenn nicht editierbar
        if (!$this->record->isEditable()) {
            return array_filter($actions, function ($action) {
                return $action->getName() !== 'save';
            });
        }

        return $actions;
    }
}
