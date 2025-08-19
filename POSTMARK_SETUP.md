# Postmark Email-Service Setup

## 1. Postmark Account einrichten

Du hast bereits einen Postmark Account. Hier die nächsten Schritte:

### Server erstellen
1. Logge dich ein bei: https://account.postmarkapp.com
2. Erstelle zwei Server:
   - **Development Server** (für lokale Entwicklung)
   - **Production Server** (für Live-System)

### Server Tokens erhalten
Für jeden Server erhältst du einen **Server API Token** (beginnt mit einem UUID-Format).

## 2. Lokale Entwicklung konfigurieren

### .env anpassen
```env
# Postmark Configuration
MAIL_MAILER=postmark
POSTMARK_TOKEN=dein-development-server-token-hier

# Optional: Für spezielle Message Streams
# POSTMARK_MESSAGE_STREAM_ID=outbound

# Absender-Einstellungen
MAIL_FROM_ADDRESS=noreply@fuerstenfeld.de
MAIL_FROM_NAME="Markt Fürstenfeld"
```

### Absender-Domains verifizieren

Du kannst **mehrere Domains** in einem Postmark Server verifizieren! Empfohlen:

1. **fuerstenfeld.de** - Für offizielle Markt-Emails
2. **sistecs.de** - Für System/Admin-Benachrichtigungen

#### Domain hinzufügen (für jede Domain wiederholen)
1. In Postmark Dashboard → "Sender Signatures" → "Add Domain"
2. Domain eingeben (z.B. `fuerstenfeld.de`)
3. DNS-Records konfigurieren:
   - **SPF**: `v=spf1 a mx include:spf.mtasv.net ~all`
   - **DKIM**: Postmark gibt dir einen speziellen DKIM-Record
   - **Return-Path**: Optional aber empfohlen für bessere Zustellraten

#### Vorteile mehrerer Domains
- **Flexibilität**: Verschiedene Absender je nach Email-Typ
- **Branding**: `noreply@fuerstenfeld.de` für Kunden, `system@sistecs.de` für Admins
- **Fallback**: Falls eine Domain Probleme hat, andere nutzen
- **Testing**: Test-Emails von anderer Domain senden

#### Verwendung im Code
```php
// In verschiedenen Mailables verschiedene Absender nutzen
class KundenEmail extends Mailable {
    public function envelope() {
        return new Envelope(
            from: new Address('noreply@fuerstenfeld.de', 'Markt Fürstenfeld'),
        );
    }
}

class AdminNotification extends Mailable {
    public function envelope() {
        return new Envelope(
            from: new Address('system@sistecs.de', 'Markt System'),
        );
    }
}
```

## 3. Test-Email senden

### Artisan Command testen
```bash
php artisan config:clear
php artisan anfragen:daily-summary --test
```

### Manueller Test mit Tinker
```bash
php artisan tinker
```

```php
use Illuminate\Support\Facades\Mail;

Mail::raw('Test Email von Postmark', function($message) {
    $message->to('test@example.com')
            ->subject('Postmark Test');
});
```

## 4. Production Setup

### Production .env
```env
MAIL_MAILER=postmark
POSTMARK_TOKEN=dein-production-server-token-hier
MAIL_FROM_ADDRESS=noreply@fuerstenfeld.de
MAIL_FROM_NAME="Markt Fürstenfeld"
```

### Deployment
```bash
# Lokal committen
git add .
git commit -m "feat: Postmark Email-Service integriert"
git push

# Auf Server
ssh fuersti@www188.your-server.de -p 222
cd /usr/home/fuersti/public_html/markt.fuerstenfeld.de
nano .env  # POSTMARK_TOKEN hinzufügen
php84 artisan config:clear
```

## 5. Monitoring & Features

### Postmark Dashboard Features
- **Activity Feed**: Alle gesendeten Emails einsehen
- **Bounce Management**: Automatisches Bounce-Handling
- **Templates**: Email-Templates direkt in Postmark verwalten (optional)
- **Webhooks**: Events an deine App senden (Bounces, Spam, etc.)
- **Stats**: Detaillierte Versand-Statistiken

### Message Streams
Postmark unterstützt verschiedene Message Streams:
- **Transactional** (Standard): Für System-Emails
- **Broadcast**: Für Marketing/Newsletter (separater Stream)

### Inbound Email Processing
Postmark kann auch eingehende Emails verarbeiten und an Webhooks weiterleiten.

## 6. Troubleshooting

### Email kommt nicht an
1. Prüfe Postmark Activity Feed
2. Überprüfe Domain-Verifizierung
3. Teste mit Postmark API direkt:
```bash
curl "https://api.postmarkapp.com/email" \
  -X POST \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "X-Postmark-Server-Token: dein-token" \
  -d '{
    "From": "noreply@fuerstenfeld.de",
    "To": "test@example.com",
    "Subject": "Test",
    "TextBody": "Test Email"
  }'
```

### Fehler "Sender signature not found"
- Absender-Email muss in Postmark verifiziert sein
- Entweder einzelne Email-Adresse oder ganze Domain verifizieren

### Rate Limits
- Development: 10 Emails/Sekunde
- Production: Abhängig vom Plan (meist 250+ Emails/Sekunde)

## 7. Kosten

### Free Tier
- 100 Emails/Monat kostenlos
- Perfekt für Development

### Production Pricing
- $15/Monat für 10.000 Emails
- $0.50 pro 1000 zusätzliche Emails
- Siehe: https://postmarkapp.com/pricing

## 8. Migration von SMTP zu Postmark

### Vorteile
- ✅ Bessere Zustellraten (kein Spam-Score Problem mehr)
- ✅ Detailliertes Tracking & Analytics
- ✅ Bounce & Spam Handling
- ✅ Schnellere Email-Zustellung
- ✅ Webhook-Support
- ✅ Email-Templates

### Was ändert sich für den Code?
- Nichts! Laravel abstrahiert den Mail-Driver
- Nur .env Konfiguration ändern
- Alle Mailables funktionieren weiterhin

## 9. Nächste Schritte

1. **Jetzt**: Development Server Token in .env eintragen
2. **Test**: Daily Summary Email testen
3. **Domain**: fuerstenfeld.de in Postmark verifizieren
4. **Production**: Production Server einrichten
5. **Deploy**: Änderungen auf Production deployen