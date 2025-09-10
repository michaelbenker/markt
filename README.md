# Markt App

## ToDo

-   Server Cronjob für Benachrichtiungen

```
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan migrate:fresh --seed
```

## Migration

php artisan make:migration name_der_migration

## Deployment

`./deploy.sh`

## Hetzner

PHP setzen
FcgidWrapper "/home/httpd/cgi-bin/php84-fcgi-starter.fcgi" .php

remote commands

```
/usr/bin/php84 /usr/bin/composer install --no-dev --optimize-autoloader

/usr/bin/php84 artisan config:clear
/usr/bin/php84 artisan cache:clear
/usr/bin/php84 artisan config:cache
/usr/bin/php84 artisan route:cache

/usr/bin/php84 artisan migrate:fresh --seed

/usr/bin/php84 artisan tinker

> \App\Models\User::where('email', 'mb@sistecs.de')->first();
```

## User über PHP Artisan Tinker anlegen:

### SSH auf den Produktionsserver

ssh user@server

### In das Projektverzeichnis wechseln

cd /pfad/zum/projekt

### Tinker starten

php artisan tinker

### Neuen User anlegen

use App\Models\User;
use Illuminate\Support\Facades\Hash;

User::create([
'name' => 'Neuer User',
'email' => 'email@example.de',
'password' => Hash::make('sicheres-passwort'),
'is_admin' => true
]);

### Tinker beenden

exit

## Migrationen

Ja, du kannst auf PROD eine einzelne Migration zurücksetzen und neu ausführen. Hier sind die Schritte:

⏺ Option 1: Migration rollback (wenn möglich)

# Auf PROD

php artisan migrate:rollback --step=1 # Rollt die letzte Migration zurück

# oder spezifisch:

php artisan migrate:rollback --path=database/migrations/2025_06_02_create_anfragen_table.php

# Dann neue Version deployen und:

php artisan migrate

Option 2: Manuell löschen (wenn Daten verloren gehen dürfen)

# Auf PROD - Tabelle manuell löschen

php artisan tinker
Schema::dropIfExists('anfragen');
exit

# Migration aus migrations-Tabelle entfernen

php artisan tinker
DB::table('migrations')->where('migration', '2025_06_02_create_anfragen_table')->delete();
exit

# Neue Version deployen und Migration ausführen

php artisan migrate

Option 3: Neue Migration für Änderungen

# Lokal - neue Migration für Änderungen erstellen

php artisan make:migration update_anfragen_table_changes

# Deploy und auf PROD:

php artisan migrate

⚠️ Wichtig: Option 1 & 2 löschen alle Daten in der anfragen Tabelle! Option 3 behält die Daten.

# Mails verschicken

```
curl "https://api.postmarkapp.com/email" \
  -X POST \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "X-Postmark-Server-Token: cb8a7bb4-f3a5-4489-b69f-5848187a5497" \
  -d '{
        "From": "info@sistecs.de",
        "To": "mb@sistecs.de",
        "Subject": "Hello from Postmark",
        "HtmlBody": "<strong>Hello</strong> dear Postmark user.",
        "MessageStream": "outbound"
      }'
```
