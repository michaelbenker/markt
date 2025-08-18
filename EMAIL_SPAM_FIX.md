# Email-Spam-Problem gelöst

## Problem
Emails wurden vom Server mit einem Spam-Score von 21 versendet und kamen daher nicht beim Empfänger an.

## Ursache
Domain-Mismatch zwischen SMTP-Login und Absenderadresse:
- **SMTP-Login**: `mailversand@sistecs.eu`
- **Absenderadresse (vorher)**: `info@sistecs.de`

Dies führte zu:
- SPF-Fehlern (Sender Policy Framework)
- Authentifizierungsproblemen
- Hohem Spam-Score

## Lösung
Absenderadresse auf die gleiche Domain wie der SMTP-Login geändert:
- **MAIL_FROM_ADDRESS**: `mailversand@sistecs.eu` (gleiche Domain wie SMTP-Login)

## Konfiguration anpassen

### Lokal (.env)
```env
MAIL_FROM_ADDRESS=mailversand@sistecs.eu
```

### Production Server
SSH auf den Server und .env anpassen:
```bash
ssh fuersti@www188.your-server.de -p 222
cd /usr/home/fuersti/public_html/markt.fuerstenfeld.de
nano .env
# MAIL_FROM_ADDRESS auf mailversand@sistecs.eu ändern
php artisan config:clear
php artisan anfragen:daily-summary --test
```

## Weitere Verbesserungen (optional)

### 1. SPF-Record prüfen
Für die Domain `sistecs.eu` sollte ein SPF-Record existieren, der `mail.your-server.de` als autorisierten Versender erlaubt:
```
v=spf1 include:mail.your-server.de ~all
```

### 2. DKIM einrichten
Kontaktiere deinen Hosting-Provider (Hetzner) um DKIM für die Domain zu aktivieren.

### 3. Alternative: Dedizierte Email-Domain
Erwäge eine dedizierte Domain für Transaktions-Emails:
- `noreply@fuerstenfeld.de`
- Mit korrekten SPF/DKIM/DMARC Records

### 4. Email-Service verwenden
Für bessere Zustellraten könnte ein dedizierter Email-Service verwendet werden:
- SendGrid
- Mailgun
- Amazon SES
- Postmark

## Test-Befehle

### Lokaler Test
```bash
php artisan config:clear
php artisan anfragen:daily-summary --test
```

### Production Test
```bash
ssh fuersti@www188.your-server.de -p 222
cd /usr/home/fuersti/public_html/markt.fuerstenfeld.de
php84 artisan config:clear
php84 artisan anfragen:daily-summary --test
```

### Mail-Header prüfen
Wenn die Email ankommt, prüfe die Header auf:
- SPF: PASS
- DKIM: PASS (falls eingerichtet)
- Spam-Score: Sollte unter 5 sein

## Monitoring
- Prüfe regelmäßig die Logs: `storage/logs/laravel.log`
- Überwache Bounce-Rates
- Teste mit verschiedenen Email-Providern (Gmail, Outlook, etc.)