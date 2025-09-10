<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('mail_reports', function (Blueprint $table) {
            $table->id();
            
            // Empfänger-Informationen
            $table->string('to_email');
            $table->string('to_name')->nullable();
            $table->string('cc_emails')->nullable(); // Komma-getrennte Liste
            $table->string('bcc_emails')->nullable(); // Komma-getrennte Liste
            
            // Absender-Informationen
            $table->string('from_email')->nullable();
            $table->string('from_name')->nullable();
            $table->string('reply_to')->nullable();
            
            // E-Mail-Inhalt
            $table->string('subject');
            $table->string('template_key')->nullable(); // Referenz zum verwendeten Template
            $table->text('content_preview')->nullable(); // Erste 500 Zeichen des Inhalts
            $table->json('attachments')->nullable(); // Liste der Anhänge mit Namen und Größe
            
            // Quelle der E-Mail (was hat den Versand ausgelöst)
            $table->string('source_type')->nullable(); // z.B. 'Anfrage', 'Buchung', 'Rechnung', 'Manual'
            $table->unsignedBigInteger('source_id')->nullable(); // ID des auslösenden Datensatzes
            $table->string('triggered_by')->nullable(); // Controller/Command/Job das den Versand ausgelöst hat
            $table->unsignedBigInteger('user_id')->nullable(); // User der die Aktion ausgelöst hat
            
            // Versand-Informationen
            $table->string('mail_driver'); // 'postmark', 'smtp', 'ses', 'mailgun', etc.
            $table->enum('status', ['pending', 'sent', 'failed', 'bounced', 'complained', 'opened', 'clicked']);
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('clicked_at')->nullable();
            $table->timestamp('bounced_at')->nullable();
            $table->timestamp('complained_at')->nullable();
            
            // Provider-spezifische Informationen
            $table->string('provider_message_id')->nullable()->index(); // Message-ID vom Provider (z.B. Postmark)
            $table->string('provider_message_stream')->nullable(); // Postmark MessageStream
            $table->json('provider_response')->nullable(); // Vollständige Response vom Provider
            $table->json('provider_metadata')->nullable(); // Zusätzliche Metadaten vom Provider
            
            // Fehler-Tracking
            $table->string('error_code')->nullable();
            $table->text('error_message')->nullable();
            $table->json('error_details')->nullable();
            $table->integer('retry_count')->default(0);
            $table->timestamp('last_retry_at')->nullable();
            
            // Tracking-Informationen
            $table->string('ip_address')->nullable(); // IP des Empfängers beim Öffnen
            $table->string('user_agent')->nullable(); // User-Agent beim Öffnen
            $table->string('client_type')->nullable(); // Desktop, Mobile, Webmail, etc.
            $table->string('client_name')->nullable(); // Gmail, Outlook, Apple Mail, etc.
            $table->string('client_os')->nullable(); // Windows, iOS, Android, etc.
            $table->json('click_tracking')->nullable(); // Welche Links wurden geklickt
            
            // Performance-Metriken
            $table->integer('send_duration_ms')->nullable(); // Wie lange hat der Versand gedauert
            $table->integer('size_bytes')->nullable(); // Größe der E-Mail in Bytes
            
            // Zusätzliche Metadaten
            $table->json('tags')->nullable(); // Tags für Kategorisierung
            $table->json('metadata')->nullable(); // Weitere beliebige Metadaten
            $table->text('notes')->nullable(); // Manuelle Notizen
            
            // Spam-Score und Validierung
            $table->decimal('spam_score', 3, 1)->nullable(); // Spam-Score von SpamAssassin etc.
            $table->boolean('dkim_valid')->nullable();
            $table->boolean('spf_valid')->nullable();
            $table->boolean('dmarc_valid')->nullable();
            
            // Umgebungs-Informationen
            $table->string('environment')->default(config('app.env', 'production')); // production, staging, local
            $table->string('server_hostname')->nullable();
            $table->string('app_version')->nullable();
            
            $table->timestamps();
            
            // Indizes für häufige Abfragen
            $table->index('to_email');
            $table->index('status');
            $table->index(['source_type', 'source_id']);
            $table->index('sent_at');
            $table->index('template_key');
            $table->index('user_id');
            $table->index('created_at');
            
            // Foreign Keys
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mail_reports');
    }
};