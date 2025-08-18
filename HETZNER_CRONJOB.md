# Hetzner KonsoleH Cronjob Setup für Daily Summary Mail

## Cronjob in KonsoleH einrichten

### 1. In KonsoleH einloggen

-   Gehe zu deiner KonsoleH: https://konsoleh.your-server.de
-   Logge dich mit deinen Zugangsdaten ein

### 2. Zum Cron-Manager navigieren

-   **Konfiguration** → **Cron-Manager**
-   Oder direkt: Account → Configuration → Cron Manager

### 3. Neuen Cronjob erstellen

Klicke auf **"Neuen Cronjob hinzufügen"** und fülle die Felder aus:

## Cronjob-Einstellungen

### Für die tägliche Zusammenfassung (morgens um 8:00 Uhr)

**Minute:** `0`  
**Stunde:** `8`  
**Tag:** `*`  
**Monat:** `*`  
**Wochentag:** `*`

**Befehl:**

```bash
/usr/bin/php84.bin.cli.bin.cli /usr/home/fuersti/public_html/markt.fuerstenfeld.de/artisan anfragen:daily-summary >> /usr/home/fuersti/public_html/markt.fuerstenfeld.de/storage/logs/cron.log 2>&1
```

### Alternative Zeitpunkte

#### Täglich um 7:00 Uhr

-   **Minute:** `0`
-   **Stunde:** `7`
-   **Tag:** `*`
-   **Monat:** `*`
-   **Wochentag:** `*`

#### Täglich um 9:00 Uhr

-   **Minute:** `0`
-   **Stunde:** `9`
-   **Tag:** `*`
-   **Monat:** `*`
-   **Wochentag:** `*`

#### Zweimal täglich (8:00 und 16:00 Uhr)

Erstelle zwei separate Cronjobs:

1. Morgens: `0 8 * * *`
2. Nachmittags: `0 16 * * *`

## Wichtige Pfade anpassen

Stelle sicher, dass die Pfade korrekt sind:

```bash
# PHP Binary (prüfe mit: which php84)
/usr/bin/php84.bin.cli
# oder
/usr/local/bin/php84
# oder
/opt/plesk/php/8.4/bin/php

# Artisan Pfad (dein App-Verzeichnis)
/usr/home/fuersti/public_html/markt.fuerstenfeld.de/artisan
# oder
/home/dein-user/public_html/markt/artisan
# oder
/var/www/vhosts/deine-domain.de/httpdocs/artisan
```

## Vollständige Befehlsoptionen

### Standard (an alle User)

```bash
/usr/bin/php84.bin.cli.bin.cli /usr/home/fuersti/public_html/markt.fuerstenfeld.de/artisan anfragen:daily-summary
```

### Test-Modus (nur an ersten User)

```bash
/usr/bin/php84.bin.cli.bin.cli /usr/home/fuersti/public_html/markt.fuerstenfeld.de/artisan anfragen:daily-summary --test
```

### Mit Logging

```bash
/usr/bin/php84.bin.cli.bin.cli /usr/home/fuersti/public_html/markt.fuerstenfeld.de/artisan anfragen:daily-summary >> /usr/home/fuersti/public_html/markt.fuerstenfeld.de/storage/logs/cron.log 2>&1
```

### Mit Datum im Log

```bash
echo "$(date): Starting daily summary" >> /usr/home/fuersti/public_html/markt.fuerstenfeld.de/storage/logs/cron.log && /usr/bin/php84.bin.cli.bin.cli /usr/home/fuersti/public_html/markt.fuerstenfeld.de/artisan anfragen:daily-summary >> /usr/home/fuersti/public_html/markt.fuerstenfeld.de/storage/logs/cron.log 2>&1
```

## E-Mail-Benachrichtigung bei Fehlern

In KonsoleH kannst du optional eine E-Mail-Adresse für Fehlerbenachrichtigungen angeben:

**E-Mail:** `deine-email@example.com`

## Cronjob testen

### 1. Manueller Test (SSH)

```bash
# Verbinde per SSH
ssh user@server

# Teste den Befehl
/usr/bin/php84.bin.cli.bin.cli /usr/home/fuersti/public_html/markt.fuerstenfeld.de/artisan anfragen:daily-summary --test

# Prüfe Log
tail -f /usr/home/fuersti/public_html/markt.fuerstenfeld.de/storage/logs/cron.log
```

### 2. Test-Cronjob (läuft jede Minute)

Erstelle temporär einen Test-Cronjob:

**Minute:** `*`  
**Stunde:** `*`  
**Tag:** `*`  
**Monat:** `*`  
**Wochentag:** `*`

**Befehl:**

```bash
/usr/bin/php84.bin.cli.bin.cli /usr/home/fuersti/public_html/markt.fuerstenfeld.de/artisan anfragen:daily-summary --test >> /usr/home/fuersti/public_html/markt.fuerstenfeld.de/storage/logs/cron-test.log 2>&1
```

**WICHTIG:** Nach erfolgreichem Test wieder löschen!

## Weitere nützliche Cronjobs

### Queue Worker neu starten (täglich um 3:00 Uhr)

```bash
0 3 * * * /usr/bin/php84.bin.cli /usr/home/fuersti/public_html/markt.fuerstenfeld.de/artisan queue:restart
```

### Cache leeren (wöchentlich Sonntags um 4:00 Uhr)

```bash
0 4 * * 0 /usr/bin/php84.bin.cli /usr/home/fuersti/public_html/markt.fuerstenfeld.de/artisan cache:clear
```

### Alte Logs aufräumen (monatlich am 1. um 2:00 Uhr)

```bash
0 2 1 * * find /usr/home/fuersti/public_html/markt.fuerstenfeld.de/storage/logs -name "*.log" -mtime +30 -delete
```

### Datenbank-Backup (täglich um 1:00 Uhr)

```bash
0 1 * * * mysqldump -u DB_USER -pDB_PASS DB_NAME | gzip > /backup/markt_$(date +\%Y\%m\%d).sql.gz
```

## Troubleshooting

### Cronjob läuft nicht

1. **Pfade prüfen:**

```bash
which php84
ls -la /usr/home/fuersti/public_html/markt.fuerstenfeld.de/artisan
```

2. **Berechtigungen prüfen:**

```bash
ls -la /usr/home/fuersti/public_html/markt.fuerstenfeld.de/storage/logs
chmod -R 775 /usr/home/fuersti/public_html/markt.fuerstenfeld.de/storage
```

3. **PHP Version prüfen:**

```bash
/usr/bin/php84.bin.cli -v
```

4. **Artisan direkt testen:**

```bash
cd /usr/home/fuersti/public_html/markt.fuerstenfeld.de
/usr/bin/php84.bin.cli artisan anfragen:daily-summary --test
```

### Log-Datei prüfen

```bash
# Live-Log anzeigen
tail -f /usr/home/fuersti/public_html/markt.fuerstenfeld.de/storage/logs/cron.log

# Letzte 50 Zeilen
tail -50 /usr/home/fuersti/public_html/markt.fuerstenfeld.de/storage/logs/cron.log

# Nach Fehlern suchen
grep -i error /usr/home/fuersti/public_html/markt.fuerstenfeld.de/storage/logs/cron.log
```

### Cron-Daemon Status

```bash
# Status prüfen
systemctl status cron
# oder
service cron status

# Cron-Log
tail -f /var/log/cron.log
# oder
journalctl -u cron -f
```

## Bestätigung

Nach dem Einrichten solltest du:

1. ✅ Cronjob in KonsoleH gespeichert
2. ✅ Test-Lauf durchgeführt
3. ✅ Log-Datei überprüft
4. ✅ E-Mail-Versand getestet

## Hinweise

-   Die Zeitangaben sind in **Serverzeit** (meist UTC oder Europe/Berlin)
-   Cronjobs laufen mit den Rechten des Web-Users
-   Bei Problemen prüfe die KonsoleH-Logs und Laravel-Logs
-   Der `--test` Flag sendet nur an den ersten User (gut zum Testen)
