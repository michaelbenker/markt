<?php

namespace App\Filament\Actions;

use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Checkbox;
use Filament\Support\Enums\MaxWidth;
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
            ->form([
                Section::make('E-Mail Einstellungen')
                    ->schema([
                        TextInput::make('email')
                            ->label('E-Mail-Adresse')
                            ->email()
                            ->required(),
                        
                        TextInput::make('subject')
                            ->label('Betreff')
                            ->required(),
                        
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
                            ->minHeight('20rem')
                            ->extraAttributes([
                                'style' => 'min-height: 20rem;',
                            ])
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
            ])
            ->fillForm(function ($record) {
                try {
                    // Sicherstellen, dass alle Relationen geladen sind
                    if (!$record->relationLoaded('markt') || !$record->relationLoaded('aussteller')) {
                        $record->load(['markt', 'standort', 'aussteller']);
                    }
                    
                    $aussteller = $record->aussteller;
                    
                    if (!$aussteller) {
                        \Illuminate\Support\Facades\Log::error('Aussteller nicht gefunden für Buchung: ' . $record->id);
                        return [
                            'email' => '',
                            'subject' => 'Bestätigung Ihrer Anmeldung',
                            'body' => 'Sehr geehrte Damen und Herren,\n\nhiermit bestätigen wir Ihre Buchung.',
                            'attach_pdf' => true,
                        ];
                    }
                    
                    // E-Mail Template laden
                    $template = EmailTemplate::where('key', 'aussteller_bestaetigung')
                        ->where('is_active', true)
                        ->first();
                    
                    if (!$template) {
                        \Illuminate\Support\Facades\Log::error('Template aussteller_bestaetigung nicht gefunden');
                        return [
                            'email' => trim($aussteller->email ?? ''),
                            'subject' => 'Bestätigung Ihrer Anmeldung',
                            'body' => 'Sehr geehrte Damen und Herren,\n\nhiermit bestätigen wir Ihre Buchung.',
                            'attach_pdf' => true,
                        ];
                    }

                    // Template mit Buchungsdaten rendern
                    $mailService = new MailService();
                    $data = [
                        'buchung' => $record,
                        'aussteller' => $aussteller,
                    ];
                    
                    // Template-Daten vorbereiten - verwende Reflection nur wenn nötig
                    $reflection = new \ReflectionClass($mailService);
                    $method = $reflection->getMethod('prepareTemplateData');
                    $method->setAccessible(true);
                    $processedData = $method->invoke($mailService, 'aussteller_bestaetigung', $data);
                    
                    // Template rendern
                    $rendered = $template->render($processedData);
                    
                    \Illuminate\Support\Facades\Log::info('Template erfolgreich gerendert (fillForm)', [
                        'buchung_id' => $record->id,
                        'subject' => $rendered['subject'] ?? 'KEIN BETREFF',
                        'content_length' => strlen($rendered['content'] ?? ''),
                    ]);
                    
                    // Werte direkt zurückgeben
                    return [
                        'email' => trim($aussteller->email ?? ''),
                        'subject' => $rendered['subject'] ?? 'Bestätigung Ihrer Anmeldung',
                        'body' => $rendered['content'] ?? 'Sehr geehrte Damen und Herren,\n\nhiermit bestätigen wir Ihre Buchung.',
                        'attach_pdf' => true,
                    ];
                    
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('Fehler beim Template-Rendering (fillForm)', [
                        'buchung_id' => $record->id,
                        'error' => $e->getMessage(),
                    ]);
                    
                    // Fallback-Werte zurückgeben
                    return [
                        'email' => trim($record->aussteller?->email ?? ''),
                        'subject' => 'Bestätigung Ihrer Anmeldung',
                        'body' => 'Sehr geehrte Damen und Herren,\n\nhiermit bestätigen wir Ihre Buchung.',
                        'attach_pdf' => true,
                    ];
                }
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
                    
                    // Source und Metadata für Mail-Tracking setzen
                    $mailService->setSource('Buchung', $record->id, 'EmailSendAction@sendBestaetigung');
                    $mailService->setMetadata([
                        'template_key' => 'aussteller_bestaetigung',
                        'action' => 'manual_confirmation',
                        'markt_id' => $record->markt_id,
                    ]);
                    
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