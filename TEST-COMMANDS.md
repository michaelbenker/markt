# TEST-BEFEHLE FÜR ENTWICKLUNG

## Lokale Tests

```bash
# Test-E-Mail senden (nur an ersten User)
php artisan anfragen:daily-summary --test

# Produktions-E-Mail senden (an alle User)
php artisan anfragen:daily-summary

# Command-Liste anzeigen
php artisan list | grep anfragen
```

## Cron-Test lokal (macOS/Linux)

```bash
# Aktuelle Crontab anzeigen
crontab -l

# Lokale Crontab bearbeiten (für Tests)
crontab -e

# Beispiel-Eintrag für Tests (jede Minute):
# * * * * * cd /Users/michaelbenker/dev/markt && php artisan anfragen:daily-summary --test >> /tmp/markt-test.log 2>&1

# Log verfolgen
tail -f /tmp/markt-test.log
```
