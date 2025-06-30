<?php

namespace App\Filament\Resources\RechnungResource\Pages;

use App\Filament\Resources\RechnungResource;
use App\Models\Rechnung;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

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
                    // E-Mail senden
                    \Illuminate\Support\Facades\Mail::send(
                        new \App\Mail\RechnungMail($this->record)
                    );

                    // Status auf "sent" setzen
                    $this->record->update([
                        'status' => 'sent',
                        'versendet_am' => now(),
                    ]);

                    Notification::make()
                        ->title('Rechnung wurde versendet')
                        ->success()
                        ->send();

                    return redirect()->route('filament.admin.resources.rechnungen.edit', $this->record);
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

                    return redirect()->route('filament.admin.resources.rechnungen.edit', $this->record);
                });
        }

        // Stornieren
        if (in_array($this->record->status, ['draft', 'sent'])) {
            $actions[] = Actions\Action::make('cancel')
                ->label('Stornieren')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Rechnung stornieren')
                ->modalDescription('Sind Sie sicher, dass Sie diese Rechnung stornieren möchten?')
                ->action(function () {
                    $this->record->update(['status' => 'canceled']);

                    Notification::make()
                        ->title('Rechnung wurde storniert')
                        ->success()
                        ->send();

                    return redirect()->route('filament.admin.resources.rechnungen.edit', $this->record);
                });
        }

        // Löschen (nur bei Draft)
        if ($this->record->status === 'draft') {
            $actions[] = Actions\ActionGroup::make([
                Actions\DeleteAction::make(),
            ]);
        }

        return $actions;
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
