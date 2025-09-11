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

echo "🔑 [3/6] Application key übersprungen (bereits vorhanden)..."
  # $PHP artisan key:generate  # AUSKOMMENTIERT FÜR PROD

echo "🧩 [4/6] Konfiguration & Routen cachen..."
$PHP artisan config:cache
$PHP artisan route:cache

echo "📂 [5/7] Storage-Verknüpfung..."
$PHP artisan storage:link

echo "📦 [6/7] Publiziere Assets..."
$PHP artisan livewire:publish --assets
$PHP artisan filament:assets

echo "✅ [7/7] Setup abgeschlossen!"