<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class MailReport extends Model
{
    use HasFactory;

    protected $fillable = [
        // Empfänger
        'to_email',
        'to_name',
        'cc_emails',
        'bcc_emails',
        
        // Absender
        'from_email',
        'from_name',
        'reply_to',
        
        // Inhalt
        'subject',
        'template_key',
        'content_preview',
        'attachments',
        
        // Quelle
        'source_type',
        'source_id',
        'triggered_by',
        'user_id',
        
        // Versand
        'mail_driver',
        'status',
        'sent_at',
        'failed_at',
        'opened_at',
        'clicked_at',
        'bounced_at',
        'complained_at',
        
        // Provider
        'provider_message_id',
        'provider_message_stream',
        'provider_response',
        'provider_metadata',
        
        // Fehler
        'error_code',
        'error_message',
        'error_details',
        'retry_count',
        'last_retry_at',
        
        // Tracking
        'ip_address',
        'user_agent',
        'client_type',
        'client_name',
        'client_os',
        'click_tracking',
        
        // Performance
        'send_duration_ms',
        'size_bytes',
        
        // Metadaten
        'tags',
        'metadata',
        'notes',
        
        // Spam & Validierung
        'spam_score',
        'dkim_valid',
        'spf_valid',
        'dmarc_valid',
        
        // Umgebung
        'environment',
        'server_hostname',
        'app_version',
    ];

    protected $casts = [
        'attachments' => 'array',
        'provider_response' => 'array',
        'provider_metadata' => 'array',
        'error_details' => 'array',
        'click_tracking' => 'array',
        'tags' => 'array',
        'metadata' => 'array',
        'sent_at' => 'datetime',
        'failed_at' => 'datetime',
        'opened_at' => 'datetime',
        'clicked_at' => 'datetime',
        'bounced_at' => 'datetime',
        'complained_at' => 'datetime',
        'last_retry_at' => 'datetime',
        'spam_score' => 'decimal:1',
        'dkim_valid' => 'boolean',
        'spf_valid' => 'boolean',
        'dmarc_valid' => 'boolean',
    ];

    /**
     * Beziehung zum User der die E-Mail ausgelöst hat
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Polymorphe Beziehung zur Quelle (kann null sein für manuelle E-Mails)
     */
    public function getSourceAttribute()
    {
        if (!$this->source_type || !$this->source_id) {
            return null;
        }

        $modelClass = $this->getSourceModelClass();
        if (!$modelClass || !class_exists($modelClass)) {
            return null;
        }

        return $modelClass::find($this->source_id);
    }

    /**
     * Hilfsmethode um die Model-Klasse aus source_type zu ermitteln
     */
    protected function getSourceModelClass(): ?string
    {
        $mapping = [
            'Anfrage' => \App\Models\Anfrage::class,
            'Buchung' => \App\Models\Buchung::class,
            'Rechnung' => \App\Models\Rechnung::class,
            'Aussteller' => \App\Models\Aussteller::class,
            'User' => \App\Models\User::class,
        ];

        return $mapping[$this->source_type] ?? null;
    }

    /**
     * Scope für erfolgreiche E-Mails
     */
    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }

    /**
     * Scope für fehlgeschlagene E-Mails
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope für E-Mails die geöffnet wurden
     */
    public function scopeOpened($query)
    {
        return $query->where('status', 'opened');
    }

    /**
     * Scope für E-Mails mit Bounces
     */
    public function scopeBounced($query)
    {
        return $query->where('status', 'bounced');
    }

    /**
     * Scope für E-Mails eines bestimmten Templates
     */
    public function scopeTemplate($query, string $templateKey)
    {
        return $query->where('template_key', $templateKey);
    }

    /**
     * Scope für E-Mails einer bestimmten Quelle
     */
    public function scopeSource($query, string $type, ?int $id = null)
    {
        $query->where('source_type', $type);
        
        if ($id !== null) {
            $query->where('source_id', $id);
        }
        
        return $query;
    }

    /**
     * Scope für E-Mails an einen bestimmten Empfänger
     */
    public function scopeToEmail($query, string $email)
    {
        return $query->where('to_email', $email);
    }

    /**
     * Markiere E-Mail als versendet
     */
    public function markAsSent(?array $providerResponse = null): void
    {
        $this->update([
            'status' => 'sent',
            'sent_at' => now(),
            'provider_response' => $providerResponse,
        ]);
    }

    /**
     * Markiere E-Mail als fehlgeschlagen
     */
    public function markAsFailed(string $errorCode, string $errorMessage, ?array $errorDetails = null): void
    {
        $this->update([
            'status' => 'failed',
            'failed_at' => now(),
            'error_code' => $errorCode,
            'error_message' => $errorMessage,
            'error_details' => $errorDetails,
        ]);
    }

    /**
     * Markiere E-Mail als geöffnet
     */
    public function markAsOpened(?string $ipAddress = null, ?string $userAgent = null): void
    {
        // Nur beim ersten Öffnen aktualisieren
        if (!$this->opened_at) {
            $this->update([
                'status' => 'opened',
                'opened_at' => now(),
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
            ]);
        }
    }

    /**
     * Markiere E-Mail als geklickt
     */
    public function markAsClicked(string $url, ?string $ipAddress = null): void
    {
        $clickTracking = $this->click_tracking ?? [];
        $clickTracking[] = [
            'url' => $url,
            'clicked_at' => now()->toIso8601String(),
            'ip_address' => $ipAddress,
        ];

        $this->update([
            'status' => 'clicked',
            'clicked_at' => $this->clicked_at ?? now(),
            'click_tracking' => $clickTracking,
        ]);
    }

    /**
     * Markiere E-Mail als bounced
     */
    public function markAsBounced(string $bounceType, string $description): void
    {
        $this->update([
            'status' => 'bounced',
            'bounced_at' => now(),
            'error_code' => $bounceType,
            'error_message' => $description,
        ]);
    }

    /**
     * Markiere E-Mail als Spam-Beschwerde
     */
    public function markAsComplained(): void
    {
        $this->update([
            'status' => 'complained',
            'complained_at' => now(),
        ]);
    }

    /**
     * Erhöhe Retry-Counter
     */
    public function incrementRetry(): void
    {
        $this->increment('retry_count');
        $this->update(['last_retry_at' => now()]);
    }

    /**
     * Berechne und speichere die E-Mail-Größe
     */
    public function calculateSize(string $content, array $attachments = []): void
    {
        $size = strlen($content);
        
        foreach ($attachments as $attachment) {
            if (isset($attachment['size'])) {
                $size += $attachment['size'];
            }
        }
        
        $this->update(['size_bytes' => $size]);
    }

    /**
     * Füge Provider-spezifische Daten hinzu (z.B. von Postmark)
     */
    public function updateProviderData(array $data): void
    {
        $updates = [];
        
        // Postmark-spezifische Felder
        if (isset($data['MessageID'])) {
            $updates['provider_message_id'] = $data['MessageID'];
        }
        if (isset($data['MessageStream'])) {
            $updates['provider_message_stream'] = $data['MessageStream'];
        }
        
        // Generische Provider-Response
        $updates['provider_response'] = $data;
        
        $this->update($updates);
    }

    /**
     * Formatiere die Attachments für die Anzeige
     */
    public function getFormattedAttachmentsAttribute(): array
    {
        if (!$this->attachments) {
            return [];
        }

        return collect($this->attachments)->map(function ($attachment) {
            return [
                'name' => $attachment['name'] ?? 'Unbekannt',
                'size' => isset($attachment['size']) 
                    ? $this->formatBytes($attachment['size']) 
                    : 'Unbekannt',
                'type' => $attachment['type'] ?? 'Unbekannt',
            ];
        })->toArray();
    }

    /**
     * Hilfsmethode für Byte-Formatierung
     */
    protected function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $factor = floor((strlen($bytes) - 1) / 3);
        
        return sprintf("%.2f", $bytes / pow(1024, $factor)) . ' ' . $units[$factor];
    }

    /**
     * Prüfe ob E-Mail retry-fähig ist
     */
    public function canRetry(): bool
    {
        return $this->status === 'failed' 
            && $this->retry_count < 3 
            && !in_array($this->error_code, ['invalid_email', 'unsubscribed', 'hard_bounce']);
    }

    /**
     * Generiere eine Zusammenfassung für das Dashboard
     */
    public static function getDashboardStats(): array
    {
        $today = now()->startOfDay();
        $thisMonth = now()->startOfMonth();
        
        return [
            'today' => [
                'sent' => self::sent()->where('sent_at', '>=', $today)->count(),
                'failed' => self::failed()->where('failed_at', '>=', $today)->count(),
                'opened' => self::opened()->where('opened_at', '>=', $today)->count(),
                'clicked' => self::where('clicked_at', '>=', $today)->count(),
            ],
            'month' => [
                'sent' => self::sent()->where('sent_at', '>=', $thisMonth)->count(),
                'failed' => self::failed()->where('failed_at', '>=', $thisMonth)->count(),
                'opened' => self::opened()->where('opened_at', '>=', $thisMonth)->count(),
                'clicked' => self::where('clicked_at', '>=', $thisMonth)->count(),
                'bounced' => self::bounced()->where('bounced_at', '>=', $thisMonth)->count(),
                'complained' => self::where('complained_at', '>=', $thisMonth)->count(),
            ],
            'open_rate' => self::calculateOpenRate($thisMonth),
            'click_rate' => self::calculateClickRate($thisMonth),
        ];
    }

    /**
     * Berechne Öffnungsrate
     */
    protected static function calculateOpenRate($since): float
    {
        $sent = self::sent()->where('sent_at', '>=', $since)->count();
        if ($sent === 0) return 0;
        
        $opened = self::opened()->where('sent_at', '>=', $since)->count();
        return round(($opened / $sent) * 100, 2);
    }

    /**
     * Berechne Klickrate
     */
    protected static function calculateClickRate($since): float
    {
        $sent = self::sent()->where('sent_at', '>=', $since)->count();
        if ($sent === 0) return 0;
        
        $clicked = self::where('status', 'clicked')->where('sent_at', '>=', $since)->count();
        return round(($clicked / $sent) * 100, 2);
    }
}