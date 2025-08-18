# GitHub Actions Deployment Pipeline

## Übersicht

Die Pipeline automatisiert das Deployment der Markt-Verwaltung Anwendung. Sie läuft automatisch bei jedem Push auf `master`/`main` oder kann manuell ausgelöst werden.

## Pipeline-Schritte

1. **Test**: Führt alle Tests mit MySQL aus
2. **Build**: Erstellt ein optimiertes Production-Build
3. **Deploy**: Lädt die Anwendung auf den Server
4. **Notify**: Sendet Benachrichtigungen über den Status

## GitHub Secrets einrichten

Gehe zu **Settings → Secrets and variables → Actions** in deinem GitHub Repository und füge folgende Secrets hinzu:

### Erforderliche Secrets

| Secret Name | Beschreibung | Beispiel |
|------------|--------------|----------|
| `DEPLOY_HOST` | Server IP oder Domain | `123.456.789.0` oder `server.example.com` |
| `DEPLOY_USER` | SSH Benutzername | `deploy` oder `www-data` |
| `DEPLOY_KEY` | Privater SSH Key (vollständig) | `-----BEGIN RSA PRIVATE KEY-----...` |
| `DEPLOY_PATH` | Absoluter Pfad zum App-Verzeichnis | `/var/www/markt` |

### Optionale Secrets

| Secret Name | Beschreibung | Standard |
|------------|--------------|----------|
| `DEPLOY_PORT` | SSH Port | `22` |
| `BACKUP_PATH` | Pfad für Backups | `/home/backup/markt` |
| `PHP_BIN` | PHP Binary Pfad | `/usr/bin/php84` |

## Environment Variables einrichten

Gehe zu **Settings → Environments** und erstelle ein `production` Environment:

### Variables

| Variable Name | Beschreibung | Beispiel |
|--------------|--------------|----------|
| `APP_URL` | Produktions-URL der App | `https://markt.example.com` |

## SSH Key erstellen

Falls du noch keinen SSH Key hast:

```bash
# Auf deinem lokalen Rechner
ssh-keygen -t rsa -b 4096 -C "github-deploy" -f ~/.ssh/github_deploy_key

# Public Key auf Server kopieren
ssh-copy-id -i ~/.ssh/github_deploy_key.pub user@server

# Oder manuell auf dem Server
cat ~/.ssh/github_deploy_key.pub >> ~/.ssh/authorized_keys
```

Den **privaten** Key (`github_deploy_key`, NICHT `.pub`) als `DEPLOY_KEY` Secret hinzufügen.

## Server-Vorbereitung

Auf dem Production Server:

```bash
# Benutzer für Deployment (falls noch nicht vorhanden)
sudo useradd -m -s /bin/bash deploy
sudo usermod -aG www-data deploy

# Verzeichnisse erstellen
sudo mkdir -p /var/www/markt
sudo mkdir -p /home/backup/markt
sudo chown -R deploy:www-data /var/www/markt
sudo chown -R deploy:deploy /home/backup/markt
sudo chmod -R 775 /var/www/markt

# .env Datei vorbereiten
sudo -u deploy nano /var/www/markt/.env
# Füge deine Production-Einstellungen ein

# PHP 8.4 sicherstellen
php84 -v

# Composer global installieren (falls noch nicht vorhanden)
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

## Erste Deployment

1. **Secrets in GitHub hinzufügen** (siehe oben)

2. **Environment erstellen** in GitHub

3. **Manuelles Deployment auslösen**:
   - Gehe zu **Actions** → **Deploy to Production**
   - Klicke auf **Run workflow**
   - Wähle Branch und Environment
   - Klicke auf **Run workflow**

## Automatisches Deployment

Nach dem Setup wird automatisch deployed bei:
- Push auf `master` oder `main` Branch
- Erfolgreichen Tests

## Rollback

Bei Problemen:

```bash
# Auf dem Server
cd /home/backup/markt
ls -la  # Liste alle Backups

# Letztes Backup wiederherstellen
cd /var/www/markt
php84 artisan down
tar -xzf /home/backup/markt/backup_TIMESTAMP.tar.gz -C .
php84 artisan up
```

## Monitoring

Die Pipeline sendet Benachrichtigungen bei:
- ✅ Erfolgreichem Deployment
- ❌ Fehlgeschlagenem Deployment

### Slack/Discord Integration (Optional)

Füge in der `deploy.yml` bei den Notify-Steps hinzu:

```yaml
- name: Send Slack notification
  uses: 8398a7/action-slack@v3
  with:
    status: ${{ job.status }}
    webhook_url: ${{ secrets.SLACK_WEBHOOK }}
```

## Troubleshooting

### SSH Key Probleme
```bash
# Teste SSH-Verbindung lokal
ssh -i ~/.ssh/github_deploy_key deploy@server
```

### Berechtigungsprobleme
```bash
# Auf dem Server
sudo chown -R www-data:www-data /var/www/markt/storage
sudo chmod -R 775 /var/www/markt/storage
```

### PHP Version
```bash
# Prüfe PHP Version
/usr/bin/php84 -v
which php84
```

## Lokales Testing

Test die Pipeline lokal mit [act](https://github.com/nektos/act):

```bash
# Install act
brew install act  # macOS
# oder siehe https://github.com/nektos/act#installation

# Test workflow
act -n  # Dry run
act push  # Simuliere push event
```

## Sicherheit

- **Niemals** Secrets im Code committen
- SSH Keys regelmäßig rotieren
- Deployment User hat minimale Rechte
- Backups werden automatisch erstellt
- Alte Backups werden automatisch gelöscht (behält nur 5)

## Erweiterte Features

### Staging Environment

Füge in `.github/workflows/deploy.yml` ein staging Environment hinzu:

1. Erstelle `staging` Environment in GitHub
2. Füge staging-spezifische Secrets hinzu
3. Die Pipeline unterstützt bereits Environment-Auswahl

### Datenbank-Backup

Erweitere das Deployment-Script:

```bash
# In deploy.yml, vor dem Backup
mysqldump -u $DB_USER -p$DB_PASS $DB_NAME > "$BACKUP_PATH/db_$TIMESTAMP.sql"
```

### Zero-Downtime Deployment

Für Zero-Downtime kannst du Laravel Envoy oder Deployer verwenden.