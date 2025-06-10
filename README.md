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
