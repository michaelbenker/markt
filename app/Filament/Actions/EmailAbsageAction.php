<?php

namespace App\Filament\Actions;

use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Section;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Support\Facades\Auth;
use App\Models\EmailTemplate;
use App\Models\BuchungProtokoll;
use App\Services\MailService;

class EmailAbsageAction extends Action
{
    public static function make(?string $name = null): static
    {
        return parent::make($name ?? 'send_absage_email')
            ->label('Absagen')
            ->icon('heroicon-o-x-circle')
            ->color('danger')
            ->modalWidth(MaxWidth::SevenExtraLarge)
            ->modalHeading('Buchung absagen')
            ->form(function ($record) {
                try {
                    // E-Mail Template laden und rendern
                    $mailService = new MailService();
                    $template = EmailTemplate::getByKey('aussteller_absage');
                    
                    if (!$template) {
                        \Illuminate\Support\Facades\Log::error('Template aussteller_absage nicht gefunden');
                        return [
                            Section::make('Fehler')
                                ->schema([
                                    \Filament\Forms\Components\Placeholder::make('error')
                                        ->content('E-Mail-Template nicht gefunden!')
                                ])
                        ];
                    }

                    // Template mit Buchungsdaten rendern
                    $buchung = $record;
                    $buchung->load(['termin.markt', 'aussteller']);
                    $aussteller = $buchung->aussteller;
                    
                    if (!$aussteller) {
                        \Illuminate\Support\Facades\Log::error('Aussteller nicht gefunden für Buchung: ' . $buchung->id);
                        return [
                            Section::make('Fehler')
                                ->schema([
                                    \Filament\Forms\Components\Placeholder::make('error')
                                        ->content('Aussteller-Daten nicht gefunden!')
                                ])
                        ];
                    }
                    
                    $data = [
                        'aussteller' => $aussteller,
                        'markt_name' => $buchung->termin->markt->name ?? 'Unbekannter Markt',
                        'termin' => ($buchung->termin && $buchung->termin->start) ? $buchung->termin->start->format('d.m.Y') : 'Unbekanntes Datum',
                        'eingereicht_am' => $buchung->created_at->format('d.m.Y')
                    ];
                    
                    // Template-Daten vorbereiten
                    $reflection = new \ReflectionClass($mailService);
                    $method = $reflection->getMethod('prepareTemplateData');
                    $method->setAccessible(true);
                    $processedData = $method->invoke($mailService, 'aussteller_absage', $data);
                    
                    $rendered = $template->render($processedData);
                    
                    \Illuminate\Support\Facades\Log::info('Absage-Template gerendert', [
                        'subject' => $rendered['subject'] ?? 'KEIN BETREFF',
                        'content_length' => strlen($rendered['content'] ?? ''),
                        'content_preview' => substr($rendered['content'] ?? '', 0, 100)
                    ]);
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('Fehler beim Absage-Template-Rendering: ' . $e->getMessage());
                    return [
                        Section::make('Fehler')
                            ->schema([
                                \Filament\Forms\Components\Placeholder::make('error')
                                    ->content('Fehler beim Laden des Templates: ' . $e->getMessage())
                            ])
                    ];
                }
                
                return [
                    Section::make('E-Mail Einstellungen')
                        ->schema([
                            TextInput::make('email')
                                ->label('E-Mail-Adresse')
                                ->email()
                                ->required()
                                ->default(trim($aussteller->email ?? '')),
                            
                            TextInput::make('subject')
                                ->label('Betreff')
                                ->required()
                                ->default($rendered['subject']),
                        ])
                        ->columns(2),
                    
                    Section::make('Absage-Nachricht')
                        ->schema([
                            MarkdownEditor::make('body')
                                ->label('Nachricht')
                                ->required()
                                ->default(function () use ($rendered) {
                                    $content = $rendered['content'] ?? '';
                                    \Illuminate\Support\Facades\Log::info('MarkdownEditor Default-Wert gesetzt', [
                                        'content_length' => strlen($content),
                                        'content_preview' => substr($content, 0, 100),
                                        'is_empty' => empty($content)
                                    ]);
                                    return $content;
                                })
                                ->toolbarButtons([
                                    'bold',
                                    'italic',
                                    'link',
                                    'heading',
                                    'bulletList',
                                    'orderedList',
                                    'blockquote',
                                    'codeBlock',
                                ]),
                        ]),
                ];
            })
            ->action(function (array $data, $record) {
                $mailService = new MailService();
                $originalStatus = $record->status;
                
                try {
                    // Custom Absage-E-Mail versenden
                    $success = $mailService->sendCustomEmail(
                        $data['email'],
                        $data['subject'],
                        $data['body'],
                        $record->aussteller->getFullName()
                    );

                    if ($success) {
                        // Nur nach erfolgreichem E-Mail-Versand Status setzen
                        $record->update(['status' => 'abgelehnt']);

                        // Protokoll-Eintrag erstellen
                        BuchungProtokoll::create([
                            'buchung_id' => $record->id,
                            'user_id' => Auth::id(),
                            'aktion' => 'buchung_abgelehnt',
                            'from_status' => $originalStatus,
                            'to_status' => 'abgelehnt',
                            'details' => 'Buchung wurde abgelehnt und Absage-E-Mail gesendet.',
                        ]);

                        \Filament\Notifications\Notification::make()
                            ->title('Absage-E-Mail erfolgreich versendet')
                            ->body('Buchungsstatus wurde auf "abgelehnt" gesetzt.')
                            ->success()
                            ->send();
                    } else {
                        \Filament\Notifications\Notification::make()
                            ->title('Fehler beim E-Mail-Versand')
                            ->body('Buchungsstatus bleibt unverändert.')
                            ->danger()
                            ->send();
                    }
                } catch (\Exception $e) {
                    \Filament\Notifications\Notification::make()
                        ->title('Fehler beim E-Mail-Versand')
                        ->body($e->getMessage() . ' Buchungsstatus bleibt unverändert.')
                        ->danger()
                        ->send();
                }
            })
            ->after(function ($livewire) {
                // Force refresh der Seite nach erfolgreichem Status-Update
                $livewire->js('window.location.reload()');
            })
            ->modalSubmitActionLabel('Absage senden')
            ->modalCancelActionLabel('Abbrechen');
    }
}