<?php

namespace App\Services;

use App\Models\EmailTemplate;
use App\Models\Rechnung;
use App\Models\Aussteller;
use App\Models\Buchung;
use App\Models\MailReport;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Str;

class MailService
{
    /**
     * Aktuelle Quelle der E-Mail für Tracking
     */
    protected ?string $sourceType = null;
    protected ?int $sourceId = null;
    protected ?string $triggeredBy = null;
    
    /**
     * Zusätzliche Metadaten für Mail-Reports
     */
    protected ?array $tags = null;
    protected ?array $metadata = null;

    /**
     * Setze die Quelle für das Mail-Tracking
     */
    public function setSource(string $type, ?int $id = null, ?string $triggeredBy = null): self
    {
        $this->sourceType = $type;
        $this->sourceId = $id;
        $this->triggeredBy = $triggeredBy;
        return $this;
    }
    
    /**
     * Setze Tags für das Mail-Tracking
     */
    public function setTags(array $tags): self
    {
        $this->tags = $tags;
        return $this;
    }
    
    /**
     * Setze Metadaten für das Mail-Tracking
     */
    public function setMetadata(array $metadata): self
    {
        $this->metadata = $metadata;
        return $this;
    }
    
    /**
     * Setze alle Tracking-Daten zurück
     */
    private function resetTrackingData(): void
    {
        $this->sourceType = null;
        $this->sourceId = null;
        $this->triggeredBy = null;
        $this->tags = null;
        $this->metadata = null;
    }

    /**
     * Zentrale Methode für alle E-Mail-Versendungen
     */
    public function send(string $templateKey, string $toEmail, array $data = [], ?string $toName = null, bool $test = false): bool
    {
        try {
            // Template laden
            $template = EmailTemplate::getByKey($templateKey);
            if (!$template || !$template->is_active) {
                throw new \Exception("E-Mail-Template '{$templateKey}' nicht gefunden oder inaktiv.");
            }

            // Dummy-Daten verwenden wenn Test-Modus aktiv
            if ($test) {
                $data = $this->getDummyData($templateKey);
            }

            // Daten vorbereiten und Template rendern
            $processedData = $this->prepareTemplateData($templateKey, $data);
            $rendered = $template->render($processedData);

            // Attachments basierend auf Template-Key ermitteln
            $attachments = $this->getAttachments($templateKey, $data);

            // Mail-Report erstellen
            $mailReport = $this->createMailReport(
                $toEmail,
                $toName,
                $rendered['subject'],
                $rendered['content'],
                $templateKey,
                $attachments
            );

            // E-Mail erstellen und versenden
            $mailable = new UniversalMail($rendered['subject'], $rendered['content'], $attachments);

            $sendStartTime = microtime(true);
            $response = $this->sendMail($toEmail, $toName, $mailable);
            $sendDuration = (int)((microtime(true) - $sendStartTime) * 1000);

            // Report mit Versand-Informationen aktualisieren
            if ($response['success']) {
                $mailReport->markAsSent($response['provider_response']);
                $mailReport->update([
                    'send_duration_ms' => $sendDuration,
                    'size_bytes' => strlen($rendered['content'])
                ]);
                
                // Provider-spezifische Daten aktualisieren
                if (isset($response['provider_response'])) {
                    $mailReport->updateProviderData($response['provider_response']);
                }
                
                // Log für Nachverfolgung
                Log::info("E-Mail versendet", [
                    'template_key' => $templateKey,
                    'to_email' => $toEmail,
                    'to_name' => $toName,
                    'attachments_count' => count($attachments),
                    'mail_report_id' => $mailReport->id
                ]);
            } else {
                $mailReport->markAsFailed(
                    $response['error_code'] ?? 'unknown',
                    $response['error_message'] ?? 'Unbekannter Fehler',
                    $response['error_details'] ?? null
                );
            }

            // Source und Metadaten zurücksetzen für nächsten Aufruf
            $this->resetTrackingData();

            return $response['success'];
        } catch (\Exception $e) {
            Log::error("Fehler beim E-Mail-Versand", [
                'template_key' => $templateKey,
                'to_email' => $toEmail,
                'error' => $e->getMessage()
            ]);

            // Mail-Report als fehlgeschlagen markieren wenn vorhanden
            if (isset($mailReport)) {
                $mailReport->markAsFailed(
                    'exception',
                    $e->getMessage(),
                    ['trace' => $e->getTraceAsString()]
                );
            }

            // Source und Metadaten zurücksetzen für nächsten Aufruf
            $this->resetTrackingData();

            return false;
        }
    }

    /**
     * Spezielle Methode für Rechnungsversand
     */
    public function sendRechnung(Rechnung $rechnung): bool
    {
        $aussteller = $rechnung->aussteller;
        if (!$aussteller || !$aussteller->email) {
            return false;
        }

        $data = [
            'rechnung' => $rechnung,
            'aussteller' => $aussteller,
        ];

        // Setze Source für Tracking
        $this->setSource('Rechnung', $rechnung->id);

        return $this->send(
            'rechnung_versand',
            $aussteller->email,
            $data,
            $aussteller->getFullName()
        );
    }

    /**
     * Spezielle Methode für Aussteller-Bestätigung
     */
    public function sendAusstellerBestaetigung(Buchung $buchung): bool
    {
        $aussteller = $buchung->aussteller;
        if (!$aussteller || !$aussteller->email) {
            return false;
        }

        // Sicherstellen, dass alle Relationen geladen sind
        $buchung->load(['markt', 'standort']);

        $data = [
            'buchung' => $buchung,
            'aussteller' => $aussteller,
        ];

        // Setze Source für Tracking mit Controller-Info
        $this->setSource('Buchung', $buchung->id, 'BuchungResource@sendBestaetigung');

        return $this->send(
            'aussteller_bestaetigung',
            $aussteller->email,
            $data,
            $aussteller->getFullName()
        );
    }

    /**
     * Spezielle Methode für Aussteller-Absage
     */
    public function sendAusstellerAbsage(Aussteller $aussteller, array $zusatzDaten = []): bool
    {
        if (!$aussteller->email) {
            return false;
        }

        $data = array_merge([
            'aussteller' => $aussteller,
        ], $zusatzDaten);

        // Nur Source setzen wenn noch keine gesetzt wurde
        // (z.B. wenn direkt von Aussteller-Seite aufgerufen)
        if (!$this->sourceType && $aussteller->id) {
            $this->setSource('Aussteller', $aussteller->id);
        }

        return $this->send(
            'aussteller_absage',
            $aussteller->email,
            $data,
            $aussteller->getFullName()
        );
    }

    /**
     * Sendet eine individuelle E-Mail mit angepasstem Inhalt
     * @return bool|array Gibt bool für Abwärtskompatibilität zurück, oder Array mit Details wenn gewünscht
     */
    public function sendCustomEmail(string $toEmail, string $subject, string $content, ?string $toName = null, array $attachments = [], bool $returnDetails = false): bool|array
    {
        $mailReport = null;
        
        try {
            // Anhänge verarbeiten
            $processedAttachments = $this->processCustomAttachments($attachments);

            // Template-Key aus Metadata holen, falls vorhanden
            $templateKey = isset($this->metadata['template_key']) ? $this->metadata['template_key'] : null;

            // Mail-Report erstellen
            $mailReport = $this->createMailReport(
                $toEmail,
                $toName,
                $subject,
                $content,
                $templateKey, // Template-Key aus Metadata, falls gesetzt
                $processedAttachments
            );

            // E-Mail erstellen und versenden
            $mailable = new UniversalMail($subject, $content, $processedAttachments);

            $sendStartTime = microtime(true);
            $response = $this->sendMail($toEmail, $toName, $mailable);
            $sendDuration = (int)((microtime(true) - $sendStartTime) * 1000);

            // Report mit Versand-Informationen aktualisieren
            if ($response['success']) {
                $mailReport->markAsSent($response['provider_response']);
                $mailReport->update([
                    'send_duration_ms' => $sendDuration,
                    'size_bytes' => strlen($content)
                ]);
                
                // Log für Nachverfolgung
                Log::info("Custom E-Mail versendet", [
                    'to_email' => $toEmail,
                    'to_name' => $toName,
                    'subject' => $subject,
                    'mail_report_id' => $mailReport->id
                ]);
            } else {
                $mailReport->markAsFailed(
                    $response['error_code'] ?? 'unknown',
                    $response['error_message'] ?? 'Unbekannter Fehler',
                    $response['error_details'] ?? null
                );
            }

            // Source und Metadaten zurücksetzen für nächsten Aufruf
            $this->resetTrackingData();

            // Details zurückgeben wenn gewünscht
            if ($returnDetails) {
                return [
                    'success' => $response['success'],
                    'mail_report_id' => $mailReport->id,
                    'mail_report' => $mailReport
                ];
            }

            return $response['success'];
        } catch (\Exception $e) {
            Log::error("Fehler beim Custom E-Mail-Versand", [
                'to_email' => $toEmail,
                'subject' => $subject,
                'error' => $e->getMessage()
            ]);

            // Mail-Report als fehlgeschlagen markieren wenn vorhanden
            if ($mailReport) {
                $mailReport->markAsFailed(
                    'exception',
                    $e->getMessage(),
                    ['trace' => $e->getTraceAsString()]
                );
            }

            // Source und Metadaten zurücksetzen für nächsten Aufruf
            $this->resetTrackingData();

            // Bei Fehler auch Details zurückgeben wenn gewünscht
            if ($returnDetails) {
                return [
                    'success' => false,
                    'mail_report_id' => $mailReport ? $mailReport->id : null,
                    'mail_report' => $mailReport,
                    'error' => $e->getMessage()
                ];
            }

            return false;
        }
    }

    /**
     * Spezielle Methode für Anfrage-Bestätigung
     */
    public function sendAnfrageBestaetigung(\App\Models\Anfrage $anfrage): bool
    {
        if (!$anfrage->email) {
            return false;
        }

        // Lade benötigte Relationen
        $anfrage->load(['markt']);

        $data = [
            'anfrage' => $anfrage,
        ];

        // Setze Source für Tracking
        $this->setSource('Anfrage', $anfrage->id);

        return $this->send(
            'anfrage_bestaetigung',
            $anfrage->email,
            $data,
            trim($anfrage->vorname . ' ' . $anfrage->nachname)
        );
    }

    /**
     * Spezielle Methode für Warteliste-Benachrichtigung
     */
    public function sendAnfrageWarteliste(\App\Models\Anfrage $anfrage, ?string $anmeldefrist = null): bool
    {
        if (!$anfrage->email) {
            return false;
        }

        // Lade benötigte Relationen
        $anfrage->load(['markt']);

        $data = [
            'anfrage' => $anfrage,
            'anmeldefrist' => $anmeldefrist,
        ];

        // Setze Source für Tracking
        $this->setSource('Anfrage', $anfrage->id, 'sendAnfrageWarteliste');

        return $this->send(
            'anfrage_warteliste',
            $anfrage->email,
            $data,
            trim($anfrage->vorname . ' ' . $anfrage->nachname)
        );
    }

    /**
     * Spezielle Methode für Aussteller-Import ohne Buchung
     */
    public function sendAnfrageAusstellerImportiert(\App\Models\Anfrage $anfrage): bool
    {
        if (!$anfrage->email) {
            return false;
        }

        // Lade benötigte Relationen
        $anfrage->load(['markt']);

        $data = [
            'anfrage' => $anfrage,
        ];

        return $this->send(
            'anfrage_aussteller_importiert',
            $anfrage->email,
            $data,
            trim($anfrage->vorname . ' ' . $anfrage->nachname)
        );
    }

    /**
     * Spezielle Methode für tägliche Anfragen-Zusammenfassung
     */
    public function sendDailyAnfragenSummary(\App\Models\User $user): bool
    {
        try {
            $mailable = new \App\Mail\TaeglicheAnfragenUebersicht();
            $this->sendMail($user->email, $user->name, $mailable);

            Log::info("Tägliche Anfragen-Zusammenfassung versendet", [
                'user_id' => $user->id,
                'email' => $user->email
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error("Fehler beim Versand der täglichen Zusammenfassung", [
                'user_id' => $user->id,
                'email' => $user->email,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    /**
     * Erstelle einen Mail-Report Eintrag
     */
    private function createMailReport(
        string $toEmail,
        ?string $toName,
        string $subject,
        string $content,
        ?string $templateKey = null,
        array $attachments = [],
        ?string $ccEmails = null,
        ?string $bccEmails = null,
        ?string $replyTo = null
    ): MailReport {
        // Attachment-Informationen für Report aufbereiten
        $attachmentInfo = [];
        foreach ($attachments as $attachment) {
            $attachmentInfo[] = [
                'name' => $attachment['name'] ?? 'unknown',
                'size' => isset($attachment['data']) ? strlen($attachment['data']) : 0,
                'type' => $attachment['mime'] ?? 'application/octet-stream'
            ];
        }

        // Backtrace analysieren um triggered_by zu ermitteln
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 10);
        $triggeredBy = $this->triggeredBy;
        
        if (!$triggeredBy) {
            foreach ($backtrace as $trace) {
                if (isset($trace['class']) && isset($trace['function'])) {
                    $class = $trace['class'];
                    // Controller oder Command finden
                    if (str_contains($class, 'Controller') || 
                        str_contains($class, 'Command') || 
                        str_contains($class, 'Resource') ||
                        str_contains($class, 'Job')) {
                        $triggeredBy = class_basename($class) . '@' . $trace['function'];
                        break;
                    }
                }
            }
        }

        // Content-Preview erstellen - Markdown beibehalten für bessere Lesbarkeit
        $contentPreview = $content;
        if (strlen($contentPreview) > 500) {
            $contentPreview = Str::limit($contentPreview, 500);
        }

        return MailReport::create([
            // Empfänger
            'to_email' => $toEmail,
            'to_name' => $toName,
            'cc_emails' => $ccEmails,
            'bcc_emails' => $bccEmails,
            
            // Absender
            'from_email' => config('mail.from.address'),
            'from_name' => config('mail.from.name'),
            'reply_to' => $replyTo ?? config('mail.reply_to.address'),
            
            // Inhalt
            'subject' => $subject,
            'template_key' => $templateKey,
            'content_preview' => $contentPreview,
            'attachments' => $attachmentInfo,
            
            // Quelle
            'source_type' => $this->sourceType,
            'source_id' => $this->sourceId,
            'triggered_by' => $triggeredBy,
            'user_id' => Auth::id(),
            
            // Versand
            'mail_driver' => config('mail.default'),
            'status' => 'pending',
            
            // Umgebung
            'environment' => config('app.env'),
            'server_hostname' => gethostname(),
            'app_version' => config('app.version', '1.0.0'),
            
            // Metadaten
            'tags' => $this->tags,
            'metadata' => $this->metadata,
        ]);
    }

    /**
     * Zentrale E-Mail Versendung mit Dev-Redirect Logik und Report-Tracking
     */
    private function sendMail(string $toEmail, ?string $toName, Mailable $mailable): array
    {
        try {
            // Im Development-Modus alle E-Mails an MAIL_DEV_REDIRECT_EMAIL umleiten
            $devRedirectEmail = env('MAIL_DEV_REDIRECT_EMAIL');
            $actualRecipient = $devRedirectEmail ?: $toEmail;
            $actualName = $devRedirectEmail ? 'Test Recipient (Original: ' . ($toName ?: $toEmail) . ')' : $toName;

            if ($devRedirectEmail) {
                Log::info("E-Mail Dev-Redirect aktiv", [
                    'original_email' => $toEmail,
                    'original_name' => $toName,
                    'redirect_email' => $devRedirectEmail
                ]);
            }

            // Provider-spezifische Versendung
            $response = $this->sendViaProvider($actualRecipient, $actualName, $mailable);

            return $response;
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error_code' => 'exception',
                'error_message' => $e->getMessage(),
                'error_details' => [
                    'class' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]
            ];
        }
    }

    /**
     * Sende E-Mail über den konfigurierten Provider
     */
    private function sendViaProvider(string $toEmail, ?string $toName, Mailable $mailable): array
    {
        $driver = config('mail.default');
        
        try {
            if ($driver === 'postmark') {
                // Postmark-spezifische Versendung mit Response-Tracking
                return $this->sendViaPostmark($toEmail, $toName, $mailable);
            } else {
                // Standard Laravel Mail-Versand (SMTP, etc.)
                Mail::to($toEmail, $toName)->send($mailable);
                
                return [
                    'success' => true,
                    'provider_response' => [
                        'driver' => $driver,
                        'timestamp' => now()->toIso8601String()
                    ]
                ];
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Sende E-Mail via Postmark
     */
    private function sendViaPostmark(string $toEmail, ?string $toName, Mailable $mailable): array
    {
        try {
            // Send email via Laravel Mail
            Mail::to($toEmail, $toName)->send($mailable);
            
            // Build response
            $response = [
                'driver' => 'postmark',
                'To' => $toEmail,
                'SubmittedAt' => now()->toIso8601String(),
                'ErrorCode' => 0,
                'Message' => 'OK',
                'MessageStream' => 'outbound'
            ];
            
            // Generate a UUID for tracking
            $response['MessageID'] = Str::uuid()->toString();
            
            return [
                'success' => true,
                'provider_response' => $response
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error_code' => 'postmark_error',
                'error_message' => $e->getMessage(),
                'provider_response' => [
                    'driver' => 'postmark',
                    'error' => true
                ]
            ];
        }
    }

    /**
     * Verarbeitet benutzerdefinierte Anhänge
     */
    private function processCustomAttachments(array $attachments): array
    {
        $processedAttachments = [];

        foreach ($attachments as $attachment) {
            $type = $attachment['type'] ?? null;

            switch ($type) {
                case 'buchung_pdf':
                    $buchung = $attachment['buchung'] ?? null;
                    if ($buchung) {
                        try {
                            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.buchung', ['buchung' => $buchung]);
                            $processedAttachments[] = [
                                'data' => $pdf->output(),
                                'name' => 'anmeldebestaetigung-' . $buchung->id . '.pdf',
                                'mime' => 'application/pdf'
                            ];
                        } catch (\Exception $e) {
                            Log::error("Fehler beim Generieren der Buchungs-PDF", [
                                'buchung_id' => $buchung->id,
                                'error' => $e->getMessage()
                            ]);
                        }
                    }
                    break;

                case 'rechnung_pdf':
                    $rechnung = $attachment['rechnung'] ?? null;
                    if ($rechnung) {
                        try {
                            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.rechnung', ['rechnung' => $rechnung]);
                            $processedAttachments[] = [
                                'data' => $pdf->output(),
                                'name' => 'rechnung-' . $rechnung->rechnungsnummer . '.pdf',
                                'mime' => 'application/pdf'
                            ];
                        } catch (\Exception $e) {
                            Log::error("Fehler beim Generieren der Rechnungs-PDF", [
                                'rechnung_id' => $rechnung->id,
                                'error' => $e->getMessage()
                            ]);
                        }
                    }
                    break;
            }
        }

        return $processedAttachments;
    }

    /**
     * Bereitet Template-spezifische Daten vor
     */
    private function prepareTemplateData(string $templateKey, array $data): array
    {
        $processedData = [];

        Log::debug('Template', [
            'key' => $templateKey
        ]);

        switch ($templateKey) {
            case 'rechnung_versand':
                $rechnung = $data['rechnung'] ?? null;
                $aussteller = $data['aussteller'] ?? null;

                if ($rechnung && $aussteller) {
                    // Behandle sowohl echte Objekte als auch Dummy-Objekte
                    $ausstellerName = method_exists($aussteller, 'getFullName')
                        ? $aussteller->getFullName()
                        : ($aussteller->vorname . ' ' . $aussteller->name);

                    $marktName = 'Unbekannter Markt';
                    // Markt direkt von Buchung holen
                    if (isset($rechnung->buchung) && is_object($rechnung->buchung) && isset($rechnung->buchung->markt)) {
                        $marktName = $rechnung->buchung->markt->name;
                    }

                    $processedData = [
                        'aussteller_name' => $ausstellerName,
                        'rechnung_nummer' => $rechnung->rechnungsnummer,
                        'markt_name' => $marktName,
                        'betrag' => number_format($rechnung->bruttobetrag / 100, 2, ',', '.') . ' €',
                    ];
                }
                break;

            case 'aussteller_bestaetigung':
                $buchung = $data['buchung'] ?? null;
                $aussteller = $data['aussteller'] ?? null;

                if ($buchung && $aussteller) {
                    $ausstellerName = method_exists($aussteller, 'getFullName')
                        ? $aussteller->getFullName()
                        : ($aussteller->vorname . ' ' . $aussteller->name);

                    // Neue Termin-Logik: termine ist jetzt ein Array von Termin-IDs
                    $termine = 'Termin wird noch bekannt gegeben';

                    // Hole die tatsächlichen Termine über die Methode
                    if (method_exists($buchung, 'termineObjekte')) {
                        $terminObjekte = $buchung->termineObjekte();

                        if ($terminObjekte->count() > 0) {
                            $terminStrings = [];
                            foreach ($terminObjekte as $termin) {
                                $start = \Carbon\Carbon::parse($termin->start)->format('d.m.Y');
                                $ende = \Carbon\Carbon::parse($termin->ende)->format('d.m.Y');

                                if ($start === $ende) {
                                    $terminStrings[] = $start;
                                } else {
                                    $terminStrings[] = $start . ' - ' . $ende;
                                }
                            }
                            $termine = implode(', ', $terminStrings);
                        }
                    }

                    $standplatz = 'Wird noch zugeteilt';
                    if (isset($buchung->standplatz) && !empty($buchung->standplatz)) {
                        $standplatz = 'Stand Nr. ' . $buchung->standplatz;
                    } elseif (isset($buchung->stand) && isset($buchung->stand->bezeichnung)) {
                        $standplatz = $buchung->stand->bezeichnung;
                    }

                    // Markt direkt von Buchung holen
                    $marktName = isset($buchung->markt->name) ? $buchung->markt->name : 'Unbekannter Markt';

                    $processedData = [
                        'aussteller_name' => $ausstellerName,
                        'markt_name' => $marktName,
                        'termine' => $termine,
                        'standplatz' => $standplatz,
                    ];
                }
                break;

            case 'anfrage_bestaetigung':
                $anfrage = $data['anfrage'] ?? null;

                if ($anfrage) {
                    // Name formatieren
                    $name = trim(($anfrage->anrede ? $anfrage->anrede . ' ' : '') . $anfrage->vorname . ' ' . $anfrage->nachname);

                    // Termine formatieren
                    $termine = 'Keine Termine ausgewählt';
                    if ($anfrage->termine && count($anfrage->termine) > 0) {
                        $terminStrings = [];
                        foreach ($anfrage->termine as $termin) {
                            $start = \Carbon\Carbon::parse($termin->start)->format('d.m.Y');
                            if ($termin->ende) {
                                $ende = \Carbon\Carbon::parse($termin->ende)->format('d.m.Y');
                                $terminStrings[] = $start . ' - ' . $ende;
                            } else {
                                $terminStrings[] = $start;
                            }
                        }
                        $termine = implode(', ', $terminStrings);
                    }

                    // Warenangebot formatieren
                    $warenangebot = '-';
                    if (is_array($anfrage->warenangebot)) {
                        if (isset($anfrage->warenangebot['subkategorien'])) {
                            $subkategorienIds = $anfrage->warenangebot['subkategorien'];
                            $sonstiges = $anfrage->warenangebot['sonstiges'] ?? null;
                            $namen = [];
                            if (!empty($subkategorienIds)) {
                                $namen = \App\Models\Subkategorie::whereIn('id', $subkategorienIds)->pluck('name')->toArray();
                                if ($sonstiges && in_array(24, $subkategorienIds)) {
                                    $namen[] = "Sonstiges: " . $sonstiges;
                                }
                            } elseif ($sonstiges) {
                                $namen[] = "Sonstiges: " . $sonstiges;
                            }
                            $warenangebot = implode(", ", $namen);
                        }
                    } else {
                        $warenangebot = $anfrage->warenangebot;
                    }

                    // Bemerkung formatieren
                    $bemerkung = '';
                    if ($anfrage->bemerkung) {
                        $bemerkung = "\n**Bemerkung:**\n" . $anfrage->bemerkung;
                    }

                    $processedData = [
                        'markt_name' => $anfrage->markt->name ?? 'Unbekannter Markt',
                        'termine' => $termine,
                        'name' => $name,
                        'email' => $anfrage->email,
                        'warenangebot' => $warenangebot,
                        'bemerkung' => $bemerkung,
                    ];
                }
                break;

            case 'anfrage_warteliste':
                $anfrage = $data['anfrage'] ?? null;
                $anmeldefrist = $data['anmeldefrist'] ?? null;

                if ($anfrage) {
                    $name = trim(($anfrage->anrede ? $anfrage->anrede . ' ' : '') . $anfrage->vorname . ' ' . $anfrage->nachname);

                    $processedData = [
                        'markt_name' => $anfrage->markt->name ?? 'Unbekannter Markt',
                        'anmeldefrist' => $anmeldefrist ?: 'wird noch bekannt gegeben',
                        'name' => $name,
                    ];
                }
                break;

            case 'anfrage_aussteller_importiert':
                $anfrage = $data['anfrage'] ?? null;

                if ($anfrage) {
                    $name = trim(($anfrage->anrede ? $anfrage->anrede . ' ' : '') . $anfrage->vorname . ' ' . $anfrage->nachname);

                    $processedData = [
                        'markt_name' => $anfrage->markt->name ?? 'Unbekannter Markt',
                        'name' => $name,
                    ];
                }
                break;

            case 'aussteller_absage':
                $aussteller = $data['aussteller'] ?? null;

                if ($aussteller) {
                    // Behandle sowohl echte Objekte als auch Dummy-Objekte
                    $ausstellerName = method_exists($aussteller, 'getFullName')
                        ? $aussteller->getFullName()
                        : ($aussteller->vorname . ' ' . $aussteller->name);

                    $processedData = [
                        'aussteller_name' => $ausstellerName,
                        'markt_name' => $data['markt_name'] ?? 'Unbekannter Markt',
                        'termin' => $data['termin'] ?? 'Unbekanntes Datum',
                        'eingereicht_am' => $data['eingereicht_am'] ?? now()->format('d.m.Y'),
                        'firma' => $aussteller->firma ?? '-',
                        'warenangebot' => $aussteller->warenangebot ?? '-',
                    ];
                }
                break;

            default:
                $processedData = $data;
                break;
        }

        // Log::debug('Template-Daten für aussteller_bestaetigung', [
        //     'data_pretty' => json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        // ]);

        // Log::debug('Template-Daten für aussteller_bestaetigung', [
        //     'processedData' => json_encode($processedData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        // ]);

        return $processedData;
    }

    /**
     * Ermittelt Attachments basierend auf Template-Key
     */
    private function getAttachments(string $templateKey, array $data): array
    {
        $attachments = [];

        switch ($templateKey) {
            case 'rechnung_versand':
                $rechnung = $data['rechnung'] ?? null;
                if ($rechnung) {
                    // PDF-Rechnung als Attachment
                    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.rechnung', ['rechnung' => $rechnung]);
                    $attachments[] = [
                        'data' => $pdf->output(),
                        'name' => 'rechnung-' . $rechnung->rechnungsnummer . '.pdf',
                        'mime' => 'application/pdf'
                    ];
                }
                break;

            case 'aussteller_bestaetigung':
                $buchung = $data['buchung'] ?? null;
                if ($buchung) {
                    // Buchungsbestätigung als PDF-Attachment (gleiche wie "Buchung drucken")
                    try {
                        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.buchung', ['buchung' => $buchung]);
                        $attachments[] = [
                            'data' => $pdf->output(),
                            'name' => 'anmeldebestaetigung-' . $buchung->id . '.pdf',
                            'mime' => 'application/pdf'
                        ];
                    } catch (\Exception $e) {
                        // Falls PDF-Generierung fehlschlägt, einfach ohne Anhang senden
                        Log::warning('PDF-Generierung für Anmeldebestätigung fehlgeschlagen', [
                            'buchung_id' => $buchung->id,
                            'error' => $e->getMessage()
                        ]);
                    }
                }
                break;

            case 'aussteller_absage':
                // Normalerweise keine Attachments bei Absagen
                break;
        }

        return $attachments;
    }

    /**
     * Liefert Template-spezifische Dummy-Daten für Tests
     */
    private function getDummyData(string $templateKey): array
    {
        $baseDummyData = [
            'markt' => 'Adventsmarkt',
            'bemerkung' => 'Beim “Advent in Fürstenfeld” präsentiert sich das Klosterareal an zwei Wochenenden von seiner schönsten Seite - Lichterglanz, Leckereien, Markt, Kunst & Musik stimmen hier auf die Weihnachtszeit ein.',
            'termine' => '07.-08.12.2024',
            'eingereicht_am' => '15.11.2024',
            'warenangebot' => 'Handwerk und Kunstwerke',
            'standplatz' => 'Stand Nr. 42',
            'rechnung_nummer' => '2024001',
            'betrag' => '150,00 €',
        ];

        $ausstellerDummyData = [
            'vorname' => 'Max',
            'name' => 'Mustermann',
            'firma' => 'ABC GmbH',
            'strasse' => 'Freisinger Platz',
            'hausnummer' => 15,
            'plz' => '85354',
            'ort' => 'Erding',
            'land' => 'Deutschland',
            'telefon' => '08122 987987',
            'mobil' => '0172 6783451',
            'email' => 'max.mustermann@example.com',
            'briefanrede' => 'Sehr geehrter Herr Mustermann',
            'homepage' => "https://www.mustermann.de",
        ];

        $leistungenDummyData = [
            [
                'name' => 'Grundfläche',
                'kategorie' => 'miete',
                'bemerkung' => 'Standmiete Grundfläche (2 Meter Frontlänge x 3 Meter Standtiefe) ',
                'einheit' => 'pauschal',
                'menge' => 1,
                'preis' => 14400,
            ],
            [
                'name' => 'Wasseranschluss',
                'kategorie' => 'nebenkosten',
                'bemerkung' => '',
                'einheit' => 'stk',
                'menge' => 1,
                'preis' => 5000,
            ],
            [
                'name' => 'Stromanschluss',
                'kategorie' => 'nebenkosten',
                'bemerkung' => '',
                'einheit' => 'stk',
                'menge' => 1,
                'preis' => 5000,
            ],
            [
                'name' => 'Tisch',
                'kategorie' => 'mobiliar',
                'bemerkung' => '',
                'einheit' => 'stk',
                'menge' => 2,
                'preis' => 5000,
            ],
        ];

        switch ($templateKey) {
            case 'rechnung_versand':
                // Erstelle dummy Rechnung und Aussteller Objekte
                $dummyAussteller = new \stdClass();
                $dummyAussteller->firma = $ausstellerDummyData['firma'];
                $dummyAussteller->vorname = $ausstellerDummyData['vorname'];
                $dummyAussteller->name = $ausstellerDummyData['name'];
                $dummyAussteller->email = $ausstellerDummyData['email'];

                $dummyRechnung = new \stdClass();
                $dummyRechnung->rechnungsnummer = $baseDummyData['rechnung_nummer'];
                $dummyRechnung->bruttobetrag = 15000; // 150,00 € in Cent
                $dummyRechnung->nettobetrag = 12605; // Netto-Betrag
                $dummyRechnung->steuerbetrag = 2395; // MwSt-Betrag
                $dummyRechnung->rechnungsdatum = now();
                $dummyRechnung->lieferdatum = now()->subDays(2); // Lieferdatum
                $dummyRechnung->faelligkeitsdatum = now()->addDays(14);
                $dummyRechnung->status = 'draft';
                $dummyRechnung->id = 1;  // Füge ID für die Rechnung hinzu
                $dummyRechnung->buchung_id = 42;
                $dummyRechnung->betreff = 'Rechnung für Standmiete ' . $baseDummyData['markt'];
                $dummyRechnung->anschreiben = 'Vielen Dank für Ihre Teilnahme am ' . $baseDummyData['markt'] . '.';
                $dummyRechnung->schlussschreiben = 'Wir freuen uns auf die weitere Zusammenarbeit.';
                $dummyRechnung->zahlungsziel = '14 Tage netto';
                $dummyRechnung->gesamtrabatt_betrag = 0;
                $dummyRechnung->gesamtrabatt_prozent = 0;
                $dummyRechnung->bezahlter_betrag = 0;
                $dummyRechnung->bezahlt_am = null;

                // Empfänger-Daten (alle empf_ Felder)
                $dummyRechnung->empf_firma = $ausstellerDummyData['firma'];
                $dummyRechnung->empf_anrede = 'Herr';
                $dummyRechnung->empf_vorname = $ausstellerDummyData['vorname'];
                $dummyRechnung->empf_name = $ausstellerDummyData['name'];
                $dummyRechnung->empf_strasse = $ausstellerDummyData['strasse'];
                $dummyRechnung->empf_hausnummer = $ausstellerDummyData['hausnummer'];
                $dummyRechnung->empf_plz = $ausstellerDummyData['plz'];
                $dummyRechnung->empf_ort = $ausstellerDummyData['ort'];
                $dummyRechnung->empf_land = $ausstellerDummyData['land'];
                $dummyRechnung->empf_email = $ausstellerDummyData['email'];

                $dummyBuchung = new \stdClass();
                $dummyMarkt = new \stdClass();
                $dummyMarkt->name = $baseDummyData['markt'];
                $dummyBuchung->markt = $dummyMarkt;
                $dummyRechnung->buchung = $dummyBuchung;

                // Dummy-Positionen für die Rechnung
                $dummyPositionen = collect();
                foreach ($leistungenDummyData as $index => $leistung) {
                    $position = new \stdClass();
                    $position->position = $index + 1;
                    $position->bezeichnung = $leistung['name'];
                    $position->beschreibung = $leistung['bemerkung'];
                    $position->menge = $leistung['menge'];
                    $position->einzelpreis = $leistung['preis'];
                    $position->steuersatz = 19.00;
                    $position->nettobetrag = $leistung['preis'] * $leistung['menge'];
                    $position->bruttobetrag = round($position->nettobetrag * 1.19);
                    $dummyPositionen->push($position);
                }

                // Positionen direkt als Collection setzen
                $dummyRechnung->positionen = $dummyPositionen;

                return [
                    'rechnung' => $dummyRechnung,
                    'aussteller' => $dummyAussteller,
                ];

            case 'aussteller_bestaetigung':
                $dummyAussteller = new \stdClass();
                $dummyAussteller->vorname = $ausstellerDummyData['vorname'];
                $dummyAussteller->name = $ausstellerDummyData['name'];

                $dummyMarkt = new \stdClass();
                $dummyMarkt->name = $baseDummyData['markt'];

                $dummyBuchung = new \stdClass();
                $dummyBuchung->id = 1;  // Füge ID hinzu
                $dummyBuchung->markt = $dummyMarkt;
                $dummyBuchung->standplatz = $baseDummyData['standplatz'];
                $dummyBuchung->termine = [1, 2];  // Dummy Termin-IDs

                return [
                    'buchung' => $dummyBuchung,
                    'aussteller' => $dummyAussteller,
                ];

            case 'anfrage_warteliste':
                $dummyAnfrage = new \stdClass();
                $dummyAnfrage->anrede = 'Herr';
                $dummyAnfrage->vorname = $ausstellerDummyData['vorname'];
                $dummyAnfrage->nachname = $ausstellerDummyData['name'];
                $dummyAnfrage->email = $ausstellerDummyData['email'];

                $dummyMarkt = new \stdClass();
                $dummyMarkt->name = $baseDummyData['markt'];
                $dummyAnfrage->markt = $dummyMarkt;

                return [
                    'anfrage' => $dummyAnfrage,
                    'anmeldefrist' => '31.12.2024',
                ];

            case 'anfrage_aussteller_importiert':
                $dummyAnfrage = new \stdClass();
                $dummyAnfrage->anrede = 'Herr';
                $dummyAnfrage->vorname = $ausstellerDummyData['vorname'];
                $dummyAnfrage->nachname = $ausstellerDummyData['name'];
                $dummyAnfrage->email = $ausstellerDummyData['email'];

                $dummyMarkt = new \stdClass();
                $dummyMarkt->name = $baseDummyData['markt'];
                $dummyAnfrage->markt = $dummyMarkt;

                return [
                    'anfrage' => $dummyAnfrage,
                ];

            case 'aussteller_absage':
                $dummyAussteller = new \stdClass();
                $dummyAussteller->vorname = $ausstellerDummyData['vorname'];
                $dummyAussteller->name = $ausstellerDummyData['name'];
                $dummyAussteller->firma = $ausstellerDummyData['firma'];
                $dummyAussteller->warenangebot = $baseDummyData['warenangebot'];

                return [
                    'aussteller' => $dummyAussteller,
                    'markt_name' => $baseDummyData['markt'],
                    'termin' => $baseDummyData['termine'],
                    'eingereicht_am' => $baseDummyData['eingereicht_am'],
                ];

            default:
                return array_merge($baseDummyData, $ausstellerDummyData);
        }
    }
}

/**
 * Universelle Mailable-Klasse für alle E-Mails
 */
class UniversalMail extends Mailable
{
    public $subject;
    public $htmlContent;
    public $attachmentData;

    public function __construct(string $subject, string $content, array $attachments = [])
    {
        $this->subject = $subject;
        $this->htmlContent = $content;
        $this->attachmentData = $attachments;
    }

    public function build()
    {
        $mail = $this->subject($this->subject)
            ->markdown('emails.template-wrapper', [
                'content' => $this->htmlContent
            ]);

        // Attachments hinzufügen
        foreach ($this->attachmentData as $attachment) {
            $mail->attachData(
                $attachment['data'],
                $attachment['name'],
                ['mime' => $attachment['mime']]
            );
        }

        return $mail;
    }
}
