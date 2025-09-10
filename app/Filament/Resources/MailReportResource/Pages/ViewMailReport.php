<?php

namespace App\Filament\Resources\MailReportResource\Pages;

use App\Filament\Resources\MailReportResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Support\Enums\FontWeight;

class ViewMailReport extends ViewRecord
{
    protected static string $resource = MailReportResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Allgemeine Informationen')
                    ->schema([
                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('status')
                                    ->label('Status')
                                    ->badge()
                                    ->color(fn (string $state): string => match($state) {
                                        'sent' => 'success',
                                        'failed' => 'danger',
                                        'pending' => 'warning',
                                        'opened' => 'info',
                                        'clicked' => 'primary',
                                        'bounced' => 'gray',
                                        'complained' => 'danger',
                                        default => 'gray',
                                    })
                                    ->formatStateUsing(fn (string $state): string => match($state) {
                                        'pending' => 'Ausstehend',
                                        'sent' => 'Versendet',
                                        'failed' => 'Fehlgeschlagen',
                                        'bounced' => 'Zurückgewiesen',
                                        'complained' => 'Beschwerde',
                                        'opened' => 'Geöffnet',
                                        'clicked' => 'Geklickt',
                                        default => $state,
                                    }),
                                    
                                Infolists\Components\TextEntry::make('created_at')
                                    ->label('Erstellt am')
                                    ->dateTime('d.m.Y H:i:s'),
                                    
                                Infolists\Components\TextEntry::make('mail_driver')
                                    ->label('Mail Provider')
                                    ->badge()
                                    ->color('gray'),
                            ]),
                    ]),
                    
                Infolists\Components\Section::make('Empfänger')
                    ->columns(2)
                    ->schema([
                        Infolists\Components\TextEntry::make('to_email')
                            ->label('E-Mail Adresse')
                            ->copyable()
                            ->weight(FontWeight::Bold),
                            
                        Infolists\Components\TextEntry::make('to_name')
                            ->label('Name')
                            ->default('-'),
                            
                        Infolists\Components\TextEntry::make('cc_emails')
                            ->label('CC')
                            ->default('-'),
                            
                        Infolists\Components\TextEntry::make('bcc_emails')
                            ->label('BCC')
                            ->default('-'),
                    ]),
                    
                Infolists\Components\Section::make('Absender')
                    ->columns(2)
                    ->schema([
                        Infolists\Components\TextEntry::make('from_email')
                            ->label('Von E-Mail')
                            ->default(config('mail.from.address')),
                            
                        Infolists\Components\TextEntry::make('from_name')
                            ->label('Von Name')
                            ->default(config('mail.from.name')),
                            
                        Infolists\Components\TextEntry::make('reply_to')
                            ->label('Antwort an')
                            ->default('-'),
                    ]),
                    
                Infolists\Components\Section::make('E-Mail Inhalt')
                    ->schema([
                        Infolists\Components\TextEntry::make('subject')
                            ->label('Betreff')
                            ->weight(FontWeight::Bold),
                            
                        Infolists\Components\TextEntry::make('template_key')
                            ->label('Template')
                            ->badge()
                            ->color('info')
                            ->visible(fn ($state): bool => !empty($state)),
                            
                        Infolists\Components\Actions::make([
                            Infolists\Components\Actions\Action::make('view_content')
                                ->label('Inhalts-Vorschau anzeigen')
                                ->icon('heroicon-o-document-text')
                                ->modalHeading('E-Mail Inhalts-Vorschau')
                                ->modalWidth('7xl')
                                ->modalContent(function ($record) {
                                    $content = $record->content_preview;
                                    if (!$content) {
                                        return new \Illuminate\Support\HtmlString('<p class="text-gray-500">Kein Inhalt vorhanden</p>');
                                    }
                                    
                                    // Parse Markdown to HTML
                                    $html = \Illuminate\Support\Str::markdown($content);
                                    
                                    return new \Illuminate\Support\HtmlString('
                                        <div class="prose prose-sm max-w-none dark:prose-invert">
                                            ' . $html . '
                                        </div>
                                    ');
                                })
                                ->modalSubmitAction(false)
                                ->modalCancelActionLabel('Schließen'),
                        ])->columnSpanFull(),
                            
                        Infolists\Components\RepeatableEntry::make('formatted_attachments')
                            ->label('Anhänge')
                            ->schema([
                                Infolists\Components\TextEntry::make('name')
                                    ->label('Dateiname'),
                                Infolists\Components\TextEntry::make('size')
                                    ->label('Größe'),
                                Infolists\Components\TextEntry::make('type')
                                    ->label('Typ'),
                            ])
                            ->columns(3)
                            ->visible(fn ($record): bool => !empty($record->attachments)),
                    ]),
                    
                Infolists\Components\Section::make('Quelle & Auslöser')
                    ->columns(2)
                    ->schema([
                        Infolists\Components\TextEntry::make('source_type')
                            ->label('Quell-Typ')
                            ->badge()
                            ->color(fn (string $state = null): string => match($state) {
                                'Anfrage' => 'info',
                                'Buchung' => 'success',
                                'Rechnung' => 'warning',
                                'Aussteller' => 'primary',
                                default => 'gray',
                            })
                            ->formatStateUsing(function (string $state = null, $record): string {
                                if (!$state) return '-';
                                return $state . ($record->source_id ? ' #' . $record->source_id : '');
                            }),
                            
                        Infolists\Components\TextEntry::make('triggered_by')
                            ->label('Ausgelöst durch')
                            ->default('-'),
                            
                        Infolists\Components\TextEntry::make('user.name')
                            ->label('Benutzer')
                            ->default('-'),
                            
                        Infolists\Components\TextEntry::make('environment')
                            ->label('Umgebung')
                            ->badge()
                            ->color(fn (string $state): string => match($state) {
                                'production' => 'success',
                                'staging' => 'warning',
                                'local' => 'info',
                                default => 'gray',
                            }),
                    ]),
                    
                Infolists\Components\Section::make('Versand-Details')
                    ->columns(3)
                    ->schema([
                        Infolists\Components\TextEntry::make('sent_at')
                            ->label('Versendet am')
                            ->dateTime('d.m.Y H:i:s')
                            ->placeholder('-'),
                            
                        Infolists\Components\TextEntry::make('send_duration_ms')
                            ->label('Versand-Dauer')
                            ->formatStateUsing(fn ($state) => $state ? $state . ' ms' : '-'),
                            
                        Infolists\Components\TextEntry::make('size_bytes')
                            ->label('E-Mail Größe')
                            ->formatStateUsing(function ($state) {
                                if (!$state) return '-';
                                $units = ['B', 'KB', 'MB'];
                                $factor = floor((strlen($state) - 1) / 3);
                                return sprintf("%.2f", $state / pow(1024, $factor)) . ' ' . $units[$factor];
                            }),
                    ]),
                    
                Infolists\Components\Section::make('Tracking')
                    ->columns(2)
                    ->schema([
                        Infolists\Components\TextEntry::make('opened_at')
                            ->label('Geöffnet am')
                            ->dateTime('d.m.Y H:i:s')
                            ->icon(fn ($state) => $state ? 'heroicon-o-eye' : null)
                            ->iconColor('success')
                            ->placeholder('Nicht geöffnet')
                            ->formatStateUsing(fn ($state) => $state ? $state->format('d.m.Y H:i:s') : null),
                            
                        Infolists\Components\TextEntry::make('clicked_at')
                            ->label('Geklickt am')
                            ->dateTime('d.m.Y H:i:s')
                            ->icon(fn ($state) => $state ? 'heroicon-o-cursor-arrow-rays' : null)
                            ->iconColor('primary')
                            ->placeholder('Keine Klicks')
                            ->formatStateUsing(fn ($state) => $state ? $state->format('d.m.Y H:i:s') : null),
                            
                        Infolists\Components\TextEntry::make('bounced_at')
                            ->label('Zurückgewiesen am')
                            ->dateTime('d.m.Y H:i:s')
                            ->icon(fn ($state) => $state ? 'heroicon-o-exclamation-triangle' : null)
                            ->iconColor('warning')
                            ->placeholder('-')
                            ->formatStateUsing(fn ($state) => $state ? $state->format('d.m.Y H:i:s') : null),
                            
                        Infolists\Components\TextEntry::make('complained_at')
                            ->label('Beschwerde am')
                            ->dateTime('d.m.Y H:i:s')
                            ->icon(fn ($state) => $state ? 'heroicon-o-shield-exclamation' : null)
                            ->iconColor('danger')
                            ->placeholder('-')
                            ->formatStateUsing(fn ($state) => $state ? $state->format('d.m.Y H:i:s') : null),
                            
                        Infolists\Components\TextEntry::make('ip_address')
                            ->label('IP-Adresse (Öffnung)')
                            ->default('-'),
                            
                        Infolists\Components\TextEntry::make('user_agent')
                            ->label('User Agent')
                            ->default('-')
                            ->columnSpanFull(),
                    ]),
                    
                Infolists\Components\Section::make('Fehler-Informationen')
                    ->visible(fn ($record): bool => $record->status === 'failed')
                    ->schema([
                        Infolists\Components\TextEntry::make('failed_at')
                            ->label('Fehlgeschlagen am')
                            ->dateTime('d.m.Y H:i:s'),
                            
                        Infolists\Components\TextEntry::make('error_code')
                            ->label('Fehler-Code')
                            ->badge()
                            ->color('danger'),
                            
                        Infolists\Components\TextEntry::make('error_message')
                            ->label('Fehlermeldung')
                            ->columnSpanFull()
                            ->color('danger'),
                            
                        Infolists\Components\KeyValueEntry::make('error_details')
                            ->label('Fehler-Details')
                            ->columnSpanFull(),
                            
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('retry_count')
                                    ->label('Wiederholungsversuche')
                                    ->badge()
                                    ->color(fn (int $state): string => match(true) {
                                        $state === 0 => 'gray',
                                        $state < 3 => 'warning',
                                        default => 'danger',
                                    }),
                                    
                                Infolists\Components\TextEntry::make('last_retry_at')
                                    ->label('Letzter Versuch')
                                    ->dateTime('d.m.Y H:i:s')
                                    ->placeholder('-'),
                            ]),
                    ]),
                    
                Infolists\Components\Section::make('Provider-Response')
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        Infolists\Components\TextEntry::make('provider_message_id')
                            ->label('Provider Message ID')
                            ->copyable()
                            ->default('-'),
                            
                        Infolists\Components\TextEntry::make('provider_message_stream')
                            ->label('Message Stream')
                            ->default('-'),
                            
                        Infolists\Components\KeyValueEntry::make('provider_response')
                            ->label('Provider Response (JSON)')
                            ->columnSpanFull(),
                            
                        Infolists\Components\KeyValueEntry::make('provider_metadata')
                            ->label('Provider Metadata')
                            ->columnSpanFull()
                            ->visible(fn ($state): bool => !empty($state)),
                    ]),
                    
                Infolists\Components\Section::make('E-Mail Validierung')
                    ->columns(3)
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        Infolists\Components\TextEntry::make('spam_score')
                            ->label('Spam Score')
                            ->badge()
                            ->color(fn ($state): string => match(true) {
                                $state === null => 'gray',
                                $state < 3 => 'success',
                                $state < 5 => 'warning',
                                default => 'danger',
                            })
                            ->formatStateUsing(fn ($state) => $state !== null ? $state . '/10' : '-'),
                            
                        Infolists\Components\IconEntry::make('dkim_valid')
                            ->label('DKIM')
                            ->boolean()
                            ->trueIcon('heroicon-o-check-circle')
                            ->falseIcon('heroicon-o-x-circle')
                            ->trueColor('success')
                            ->falseColor('danger'),
                            
                        Infolists\Components\IconEntry::make('spf_valid')
                            ->label('SPF')
                            ->boolean()
                            ->trueIcon('heroicon-o-check-circle')
                            ->falseIcon('heroicon-o-x-circle')
                            ->trueColor('success')
                            ->falseColor('danger'),
                            
                        Infolists\Components\IconEntry::make('dmarc_valid')
                            ->label('DMARC')
                            ->boolean()
                            ->trueIcon('heroicon-o-check-circle')
                            ->falseIcon('heroicon-o-x-circle')
                            ->trueColor('success')
                            ->falseColor('danger'),
                    ]),
                    
                Infolists\Components\Section::make('Zusätzliche Informationen')
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        Infolists\Components\KeyValueEntry::make('tags')
                            ->label('Tags')
                            ->visible(fn ($state): bool => !empty($state)),
                            
                        Infolists\Components\KeyValueEntry::make('metadata')
                            ->label('Metadaten')
                            ->visible(fn ($state): bool => !empty($state)),
                            
                        Infolists\Components\TextEntry::make('notes')
                            ->label('Notizen')
                            ->columnSpanFull()
                            ->visible(fn ($state): bool => !empty($state)),
                            
                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('server_hostname')
                                    ->label('Server')
                                    ->default('-'),
                                    
                                Infolists\Components\TextEntry::make('app_version')
                                    ->label('App Version')
                                    ->default('-'),
                                    
                                Infolists\Components\TextEntry::make('updated_at')
                                    ->label('Zuletzt aktualisiert')
                                    ->dateTime('d.m.Y H:i:s'),
                            ]),
                    ]),
            ]);
    }
    
    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('retry')
                ->label('Erneut senden')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->requiresConfirmation()
                ->visible(fn (): bool => $this->record->status === 'failed' && $this->record->canRetry())
                ->action(function () {
                    // TODO: Implementiere Retry-Logik
                    $this->record->incrementRetry();
                    
                    \Filament\Notifications\Notification::make()
                        ->title('E-Mail wird erneut versendet')
                        ->info()
                        ->send();
                }),
                
            Actions\Action::make('view_source')
                ->label('Zur Quelle')
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->color('info')
                ->visible(fn (): bool => $this->record->source_type && $this->record->source_id)
                ->url(function () {
                    $sourceType = $this->record->source_type;
                    $sourceId = $this->record->source_id;
                    
                    return match($sourceType) {
                        'Anfrage' => route('filament.admin.resources.anfrage.view', $sourceId),
                        'Buchung' => route('filament.admin.resources.buchung.edit', $sourceId),
                        'Rechnung' => route('filament.admin.resources.rechnung.view', $sourceId),
                        'Aussteller' => route('filament.admin.resources.aussteller.edit', $sourceId),
                        default => null,
                    };
                })
                ->openUrlInNewTab(),
        ];
    }
}