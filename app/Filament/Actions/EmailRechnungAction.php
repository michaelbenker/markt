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

class EmailRechnungAction extends Action
{
    public static function make(?string $name = null): static
    {
        return parent::make($name ?? 'send_rechnung_email')
            ->label('Rechnung per E-Mail senden')
            ->icon('heroicon-o-envelope')
            ->color('success')
            ->modalWidth(MaxWidth::SevenExtraLarge)
            ->modalHeading('Rechnung per E-Mail senden')
            ->form(function ($record) {
                return [
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
                                ->label('Rechnung als PDF anhängen')
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
                ];
            })
            ->fillForm(function ($record) {
                try {
                    // E-Mail Template laden und rendern
                    $mailService = new MailService();
                    $template = EmailTemplate::getByKey('rechnung_versand');

                    if (!$template) {
                        \Illuminate\Support\Facades\Log::error('Template rechnung_versand nicht gefunden');
                        return [
                            'email' => trim($record->empf_email ?? ''),
                            'subject' => 'Rechnung',
                            'body' => 'Sehr geehrte Damen und Herren,\n\nanbei erhalten Sie die Rechnung.',
                            'attach_pdf' => true,
                        ];
                    }

                    // Template mit Rechnungsdaten rendern
                    $rechnung = $record;
                    $rechnung->load(['aussteller']);
                    $aussteller = $rechnung->aussteller;

                    if (!$aussteller) {
                        \Illuminate\Support\Facades\Log::error('Aussteller nicht gefunden für Rechnung: ' . $rechnung->id);
                        return [
                            'email' => trim($record->empf_email ?? ''),
                            'subject' => 'Rechnung',
                            'body' => 'Sehr geehrte Damen und Herren,\n\nanbei erhalten Sie die Rechnung.',
                            'attach_pdf' => true,
                        ];
                    }

                    $data = [
                        'rechnung' => $rechnung,
                        'aussteller' => $aussteller,
                    ];

                    // Template-Daten vorbereiten
                    $reflection = new \ReflectionClass($mailService);
                    $method = $reflection->getMethod('prepareTemplateData');
                    $method->setAccessible(true);
                    $processedData = $method->invoke($mailService, 'rechnung_versand', $data);

                    $rendered = $template->render($processedData);

                    \Illuminate\Support\Facades\Log::info('Rechnung-Template gerendert (fillForm)', [
                        'subject' => $rendered['subject'] ?? 'KEIN BETREFF',
                        'content_length' => strlen($rendered['content'] ?? ''),
                        'content_preview' => substr($rendered['content'] ?? '', 0, 100),
                        'environment' => app()->environment(),
                        'full_content' => $rendered['content'] ?? 'KEIN CONTENT',
                    ]);

                    // Werte direkt zurückgeben
                    return [
                        'email' => trim($record->empf_email ?? ''),
                        'subject' => $rendered['subject'] ?? 'Rechnung',
                        'body' => $rendered['content'] ?? 'Sehr geehrte Damen und Herren,\n\nanbei erhalten Sie die Rechnung.',
                        'attach_pdf' => true,
                    ];
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('Fehler beim Rechnung-Template-Rendering (fillForm): ' . $e->getMessage());
                    
                    // Fallback-Werte zurückgeben
                    return [
                        'email' => trim($record->empf_email ?? ''),
                        'subject' => 'Rechnung',
                        'body' => 'Sehr geehrte Damen und Herren,\n\nanbei erhalten Sie die Rechnung.',
                        'attach_pdf' => true,
                    ];
                }
            })
            ->action(function (array $data, $record) {
                $mailService = new MailService();

                try {
                    // Source für Mail-Tracking setzen
                    $mailService->setSource('Rechnung', $record->id, 'EmailRechnungAction@send');
                    $mailService->setMetadata([
                        'template_key' => 'rechnung_versand',
                        'action' => 'manual_send',
                    ]);

                    // PDF-Anhang vorbereiten falls gewünscht
                    $attachments = [];
                    if ($data['attach_pdf'] ?? false) {
                        $attachments[] = [
                            'type' => 'rechnung_pdf',
                            'rechnung' => $record
                        ];
                    }

                    // Custom E-Mail versenden
                    $success = $mailService->sendCustomEmail(
                        $data['email'],
                        $data['subject'],
                        $data['body'],
                        $record->empf_vorname . ' ' . $record->empf_name,
                        $attachments
                    );

                    if ($success) {
                        // Status auf "sent" setzen
                        $record->update([
                            'status' => 'sent',
                            'versendet_am' => now(),
                        ]);

                        \Filament\Notifications\Notification::make()
                            ->title('Rechnung wurde versendet')
                            ->body('Status wurde auf "versendet" gesetzt.')
                            ->success()
                            ->send();
                    } else {
                        \Filament\Notifications\Notification::make()
                            ->title('Fehler beim E-Mail-Versand')
                            ->body('Rechnungsstatus bleibt unverändert.')
                            ->danger()
                            ->send();
                    }
                } catch (\Exception $e) {
                    \Filament\Notifications\Notification::make()
                        ->title('Fehler beim E-Mail-Versand')
                        ->body($e->getMessage() . ' Rechnungsstatus bleibt unverändert.')
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
