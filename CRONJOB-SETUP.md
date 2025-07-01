# CRON-JOB SETUP FÜR PRODUKTIONSSERVER

## 1. Cronjob-Datei erstellen

Erstelle eine Datei für die Cronjob-Konfiguration:

```bash
sudo nano /etc/cron.d/markt-anfragen-summary
```

Inhalt der Datei:

```
# Tägliche Anfragen-Übersicht um 6:00 Uhr
0 6 * * * www-data cd /pfad/zu/deiner/laravel-app && php artisan anfragen:daily-summary >> /var/log/markt-cronjob.log 2>&1

# Alternative: Mit vollem PHP-Pfad (falls nötig)
# 0 6 * * * www-data cd /pfad/zu/deiner/laravel-app && /usr/bin/php artisan anfragen:daily-summary >> /var/log/markt-cronjob.log 2>&1
```

## 2. Pfade anpassen

Ersetze `/pfad/zu/deiner/laravel-app` mit dem tatsächlichen Pfad deiner Laravel-Installation.

Beispiele:

-   `/var/www/html/markt`
-   `/home/deploy/markt`
-   `/var/www/markt`

## 3. Berechtigungen setzen

```bash
# Cronjob-Datei aktivieren
sudo chmod 644 /etc/cron.d/markt-anfragen-summary

# Cron-Service neu starten
sudo service cron restart
```

## 4. Log-Datei erstellen und Berechtigungen setzen

```bash
# Log-Datei erstellen
sudo touch /var/log/markt-cronjob.log

# Berechtigungen für www-data setzen
sudo chown www-data:www-data /var/log/markt-cronjob.log
sudo chmod 644 /var/log/markt-cronjob.log
```

## 5. Testen

```bash
# Manuell testen (als www-data User)
sudo -u www-data cd /pfad/zu/deiner/laravel-app && php artisan anfragen:daily-summary --test

# Cronjob-Status prüfen
sudo service cron status

# Log-Datei überwachen
tail -f /var/log/markt-cronjob.log
```

## 6. Cron-Format Erklärung

```
0 6 * * *
│ │ │ │ │
│ │ │ │ └── Wochentag (0-7, 0=Sonntag)
│ │ │ └──── Monat (1-12)
│ │ └────── Tag (1-31)
│ └──────── Stunde (0-23)
└────────── Minute (0-59)
```

Beispiele:

-   `0 6 * * *` = Jeden Tag um 6:00 Uhr
-   `0 8 * * 1` = Jeden Montag um 8:00 Uhr
-   `30 18 * * 1-5` = Montag-Freitag um 18:30 Uhr

## 7. Troubleshooting

**Problem: Cronjob läuft nicht**

```bash
# Cron-Service prüfen
sudo systemctl status cron

# Cron-Logs prüfen
sudo tail -f /var/log/syslog | grep CRON
```

**Problem: PHP-Pfad nicht gefunden**

```bash
# PHP-Pfad herausfinden
which php

# Im Cronjob absoluten Pfad verwenden
# 0 6 * * * www-data cd /var/www/markt && /usr/bin/php artisan anfragen:daily-summary
```

**Problem: Laravel-Umgebung**
Stelle sicher, dass die `.env`-Datei korrekt konfiguriert ist und der `www-data`-User Zugriff hat.

## 8. Monitoring (Optional)

Für besseres Monitoring kannst du ein Monitoring-Tool wie "Laravel Horizon" oder externe Services verwenden, die prüfen ob der Cronjob läuft.
