#!/bin/bash

# PHP CLI (nicht FastCGI!)
PHP="/usr/bin/php84"
COMPOSER="$PHP /usr/bin/composer"

# Verzeichnis setzen
cd "$(dirname "$0")" || exit 1

echo "🧹 [0/6] Cache aufräumen..."
$PHP artisan cache:clear
$PHP artisan config:clear
$PHP artisan route:clear
$PHP artisan view:clear

echo "🗄️ [1/6] Datenbank-Migrationen ausführen..."
$PHP artisan migrate --force

echo "📦 [2/6] Composer installieren..."
$COMPOSER install --no-dev --optimize-autoloader

echo "🔑 [3/6] Application key generieren..."
$PHP artisan key:generate

echo "🧩 [4/6] Konfiguration & Routen cachen..."
$PHP artisan config:cache
$PHP artisan route:cache

echo "📂 [5/6] Storage-Verknüpfung..."
$PHP artisan storage:link

echo "✅ [6/6] Setup abgeschlossen!"