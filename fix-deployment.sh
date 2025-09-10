#!/bin/bash

# Fix für fehlende LaravelLang Dependencies und Migration

echo "🔧 Fixing deployment issues..."

# SSH-Verbindung
SSH_USER="fuersti"
SSH_SERVER="www188.your-server.de"
SSH_PORT="222"
PROJECT_DIR="/home/fuersti/public_html/markt.fuerstenfeld.de"

echo "📦 Installing missing Laravel Lang packages on server..."
ssh -p $SSH_PORT $SSH_USER@$SSH_SERVER << 'ENDSSH'
cd /home/fuersti/public_html/markt.fuerstenfeld.de

# Backup composer.json
cp composer.json composer.json.backup

# Install missing laravel-lang packages
composer require laravel-lang/actions laravel-lang/attributes laravel-lang/lang --no-interaction

# Clear all caches
php artisan config:clear
php artisan cache:clear

# Run migrations
echo "🗄️ Running migrations..."
php artisan migrate --force

# Check if MailReport table exists
php artisan tinker --execute="
if (Schema::hasTable('mail_reports')) {
    echo 'MailReport table exists!' . PHP_EOL;
    echo 'Columns: ' . implode(', ', Schema::getColumnListing('mail_reports')) . PHP_EOL;
} else {
    echo 'MailReport table does NOT exist!' . PHP_EOL;
}
"

# Cache config again
php artisan config:cache
php artisan route:cache

echo "✅ Fix completed!"
ENDSSH

echo "✅ Deployment fix applied!"