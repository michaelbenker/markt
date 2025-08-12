<?php

namespace App\Filament\Actions;

use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Form;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Support\HtmlString;
use App\Models\EmailTemplate;
use App\Services\MailService;

class EmailSendAction extends Action
{
    public static function make(?string $name = null): static
    {
        return parent::make($name ?? 'send_email')
            ->label('E-Mail senden')
            ->icon('heroicon-o-envelope')
            ->color('success')
            ->modalWidth(MaxWidth::SevenExtraLarge)
            ->modalHeading('E-Mail senden')
            ->form(function ($record) {
                try {
                    // E-Mail Template laden und rendern
                    $mailService = new MailService();
                    $template = EmailTemplate::getByKey('aussteller_bestaetigung');
                    
                    if (!$template) {
                        \Illuminate\Support\Facades\Log::error('Template aussteller_bestaetigung nicht gefunden');
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
                    $buchung->load(['markt', 'standort', 'aussteller']);
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
                        'buchung' => $buchung,
                        'aussteller' => $aussteller,
                    ];
                    
                    // Template-Daten vorbereiten
                    $reflection = new \ReflectionClass($mailService);
                    $method = $reflection->getMethod('prepareTemplateData');
                    $method->setAccessible(true);
                    $processedData = $method->invoke($mailService, 'aussteller_bestaetigung', $data);
                    
                    $rendered = $template->render($processedData);
                    
                    \Illuminate\Support\Facades\Log::info('Template gerendert', [
                        'subject' => $rendered['subject'] ?? 'KEIN BETREFF',
                        'content_length' => strlen($rendered['content'] ?? ''),
                        'content_preview' => substr($rendered['content'] ?? '', 0, 100)
                    ]);
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('Fehler beim Template-Rendering: ' . $e->getMessage());
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
                            
                            Checkbox::make('attach_pdf')
                                ->label('Buchungsbestätigung als PDF anhängen')
                                ->default(true),
                        ])
                        ->columns(2),
                    
                    Section::make('E-Mail Inhalt')
                        ->schema([
                            MarkdownEditor::make('body')
                                ->label('Nachricht')
                                ->required()
                                ->default(function () use ($rendered) {
                                    $content = $rendered['content'] ?? '';
                                    \Illuminate\Support\Facades\Log::info('EmailSend MarkdownEditor Default-Wert gesetzt', [
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
                    // PDF-Anhang vorbereiten falls gewünscht
                    $attachments = [];
                    if ($data['attach_pdf'] ?? false) {
                        $attachments[] = [
                            'type' => 'buchung_pdf',
                            'buchung' => $record
                        ];
                    }
                    
                    // Custom E-Mail versenden
                    $success = $mailService->sendCustomEmail(
                        $data['email'],
                        $data['subject'],
                        $data['body'],
                        $record->aussteller->getFullName(),
                        $attachments
                    );

                    if ($success) {
                        // Nur nach erfolgreichem E-Mail-Versand Status setzen
                        $record->update(['status' => 'bestätigt']);

                        // Protokoll-Eintrag erstellen
                        \App\Models\BuchungProtokoll::create([
                            'buchung_id' => $record->id,
                            'user_id' => \Illuminate\Support\Facades\Auth::id(),
                            'aktion' => 'bestaetigung_gesendet',
                            'from_status' => $originalStatus,
                            'to_status' => 'bestätigt',
                            'details' => 'Bestätigungs-E-Mail wurde versendet und Status auf "bestätigt" gesetzt.',
                        ]);

                        \Filament\Notifications\Notification::make()
                            ->title('E-Mail erfolgreich versendet')
                            ->body('Buchungsstatus wurde auf "bestätigt" gesetzt.')
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
                // Refresh nach E-Mail-Versand
                $livewire->js('window.location.reload()');
            })
            ->modalSubmitActionLabel('E-Mail senden')
            ->modalCancelActionLabel('Abbrechen');
    }
}