<?php

namespace App\Filament\Actions;

use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Checkbox;
use Filament\Support\Enums\MaxWidth;
use App\Models\EmailTemplate;
use App\Services\MailService;
use Illuminate\Database\Eloquent\Model;

class UniversalEmailAction extends Action
{
    protected string $templateKey;
    protected ?string $successStatus = null;
    protected ?string $attachmentType = null;
    protected ?string $protocolAction = null;
    
    public static function make(?string $name = null): static
    {
        // Prüfe ob wir auf Production sind (kein Debug-Modus)
        $isProduction = !config('app.debug');
        
        return parent::make($name ?? 'send_email')
            ->modalWidth(MaxWidth::SevenExtraLarge)
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
                            ->label('PDF anhängen')
                            ->default(true)
                            ->visible(fn($livewire) => static::hasAttachment($livewire)),
                    ])
                    ->columns(2),
                
                Section::make('E-Mail Inhalt')
                    ->schema([
                        // Verwende Textarea auf Production, MarkdownEditor lokal
                        $isProduction 
                            ? Textarea::make('body')
                                ->label('Nachricht')
                                ->required()
                                ->rows(20)
                                ->columnSpanFull()
                                ->helperText('Tipp: Sie können Markdown-Formatierung verwenden (z.B. **fett**, *kursiv*, # Überschrift)')
                            : MarkdownEditor::make('body')
                                ->label('Nachricht')
                                ->required()
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
            ->modalSubmitActionLabel('E-Mail senden')
            ->modalCancelActionLabel('Abbrechen');
    }
    
    public function template(string $key): static
    {
        $this->templateKey = $key;
        return $this;
    }
    
    public function successStatus(string $status): static
    {
        $this->successStatus = $status;
        return $this;
    }
    
    public function attachmentType(string $type): static
    {
        $this->attachmentType = $type;
        return $this;
    }
    
    public function protocolAction(string $action): static
    {
        $this->protocolAction = $action;
        return $this;
    }
    
    protected static function hasAttachment($livewire): bool
    {
        $action = $livewire->getMountedAction();
        return !empty($action->attachmentType);
    }
    
    protected function setUp(): void
    {
        parent::setUp();
        
        $this->fillForm(function ($record) {
            // Template laden - muss existieren!
            $template = EmailTemplate::getByKey($this->templateKey);
            
            if (!$template) {
                throw new \Exception("Email-Template '{$this->templateKey}' nicht gefunden! Template muss in der Datenbank existieren.");
            }
            
            // Daten für Template vorbereiten
            $data = $this->prepareTemplateData($record);
            
            // Template rendern
            $mailService = new MailService();
            $reflection = new \ReflectionClass($mailService);
            $method = $reflection->getMethod('prepareTemplateData');
            $method->setAccessible(true);
            $processedData = $method->invoke($mailService, $this->templateKey, $data);
            
            $rendered = $template->render($processedData);
            
            \Illuminate\Support\Facades\Log::info("Template {$this->templateKey} gerendert (UniversalEmailAction)", [
                'template_key' => $this->templateKey,
                'subject' => $rendered['subject'],
                'content_length' => strlen($rendered['content']),
                'content_preview' => substr($rendered['content'], 0, 100),
            ]);
            
            // Template-Content verwenden - wenn leer, ist das ein Problem mit dem Template
            return [
                'email' => $this->getRecipientEmail($record),
                'subject' => $rendered['subject'],
                'body' => $rendered['content'],
                'attach_pdf' => !empty($this->attachmentType),
            ];
        });
        
        $this->action(function (array $data, $record) {
            $mailService = new MailService();
            $originalStatus = $record->status ?? null;
            
            try {
                // Source für Mail-Tracking setzen
                $sourceType = class_basename($record);
                $mailService->setSource($sourceType, $record->id, static::class . '@' . $this->templateKey);
                $mailService->setMetadata([
                    'template_key' => $this->templateKey,
                    'action' => 'manual_send',
                ]);
                
                // Attachments vorbereiten
                $attachments = [];
                if (($data['attach_pdf'] ?? false) && $this->attachmentType) {
                    $attachments[] = [
                        'type' => $this->attachmentType,
                        class_basename($record) => $record,
                    ];
                }
                
                // E-Mail versenden
                $recipientName = $this->getRecipientName($record);
                $success = $mailService->sendCustomEmail(
                    $data['email'],
                    $data['subject'],
                    $data['body'],
                    $recipientName,
                    $attachments
                );
                
                if ($success) {
                    // Status aktualisieren wenn gewünscht
                    if ($this->successStatus) {
                        $record->update(['status' => $this->successStatus]);
                        
                        // Protokoll erstellen wenn gewünscht
                        if ($this->protocolAction && class_exists('\App\Models\BuchungProtokoll')) {
                            \App\Models\BuchungProtokoll::create([
                                'buchung_id' => $record->id,
                                'user_id' => \Illuminate\Support\Facades\Auth::id(),
                                'aktion' => $this->protocolAction,
                                'from_status' => $originalStatus,
                                'to_status' => $this->successStatus,
                                'details' => "E-Mail wurde versendet (Template: {$this->templateKey})",
                            ]);
                        }
                    }
                    
                    // Spezielle Aktionen für Rechnungen
                    if ($record instanceof \App\Models\Rechnung) {
                        $record->update([
                            'status' => 'sent',
                            'versendet_am' => now(),
                        ]);
                    }
                    
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
            } catch (\Exception $e) {
                \Filament\Notifications\Notification::make()
                    ->title('Fehler beim E-Mail-Versand')
                    ->body($e->getMessage())
                    ->danger()
                    ->send();
            }
        });
        
        $this->after(function ($livewire) {
            $livewire->js('window.location.reload()');
        });
    }
    
    protected function prepareTemplateData(Model $record): array
    {
        // Basis-Daten je nach Model-Typ
        $data = [];
        
        if ($record instanceof \App\Models\Rechnung) {
            $record->load(['aussteller']);
            $data = [
                'rechnung' => $record,
                'aussteller' => $record->aussteller,
            ];
        } elseif ($record instanceof \App\Models\Buchung) {
            $record->load(['markt', 'standort', 'aussteller']);
            $data = [
                'buchung' => $record,
                'aussteller' => $record->aussteller,
                'markt' => $record->markt,
            ];
            
            // Für Absage-Template spezielle Daten
            if ($this->templateKey === 'aussteller_absage') {
                $data['markt_name'] = $record->markt?->name ?? 'Unbekannter Markt';
                // Hole den ersten Termin des Marktes
                $termin = $record->markt?->termine?->first();
                $data['termin'] = $termin?->start?->format('d.m.Y') ?? 'Unbekanntes Datum';
                $data['eingereicht_am'] = $record->created_at->format('d.m.Y');
            }
        } elseif ($record instanceof \App\Models\Anfrage) {
            $record->load(['markt']);
            $markt = $record->markt;
            $termin = $markt?->termine?->sortBy('start')->first();
            
            // Basis-Daten für Anfrage
            $data = [
                'anfrage' => $record,
                'markt' => $markt,
                'markt_name' => $markt?->name ?? 'Unbekannter Markt',
                'termin' => $termin?->start?->format('d.m.Y') ?? 'Unbekanntes Datum',
                'eingereicht_am' => $record->created_at->format('d.m.Y'),
            ];
            
            // Erstelle temporäres Aussteller-Objekt aus Anfrage-Daten
            $aussteller = new \App\Models\Aussteller();
            $aussteller->email = $record->email;
            $aussteller->vorname = $record->vorname;
            $aussteller->name = $record->nachname;
            $aussteller->firma = $record->firma;
            $aussteller->strasse = $record->strasse;
            $aussteller->plz = $record->plz;
            $aussteller->ort = $record->ort;
            $aussteller->telefon = $record->telefon;
            
            $data['aussteller'] = $aussteller;
            
            // Für Warteliste-Template
            if ($this->templateKey === 'anfrage_warteliste') {
                // Hole den Anmeldeschluss vom nächsten zukünftigen Termin
                $termin = $markt->termine
                    ->filter(fn($t) => $t->start >= now())
                    ->sortBy('start')
                    ->first();
                $data['anmeldefrist'] = $termin->anmeldeschluss->format('d.m.Y');
            }
        }
        
        return $data;
    }
    
    protected function getRecipientEmail(Model $record): string
    {
        if ($record instanceof \App\Models\Rechnung) {
            return trim($record->empf_email ?? '');
        } elseif ($record instanceof \App\Models\Buchung) {
            return trim($record->aussteller?->email ?? '');
        } elseif ($record instanceof \App\Models\Anfrage) {
            return trim($record->email ?? '');
        }
        
        return '';
    }
    
    protected function getRecipientName(Model $record): string
    {
        if ($record instanceof \App\Models\Rechnung) {
            return $record->empf_vorname . ' ' . $record->empf_name;
        } elseif ($record instanceof \App\Models\Buchung) {
            return $record->aussteller?->getFullName() ?? 'Kunde';
        } elseif ($record instanceof \App\Models\Anfrage) {
            return $record->vorname . ' ' . $record->nachname;
        }
        
        return 'Kunde';
    }
    
}