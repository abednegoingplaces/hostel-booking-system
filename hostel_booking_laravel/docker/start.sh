#!/bin/sh
# Start-up for the live server: listen on Render's port, prepare Laravel, run Apache.
set -e

PORT="${PORT:-10000}"
sed -i "s/^Listen 80$/Listen ${PORT}/" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:80>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/000-default.conf

php artisan storage:link --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan migrate --force
php artisan db:seed --force   # does nothing once the demo data exists

chown -R www-data:www-data storage bootstrap/cache
exec apache2-foreground
