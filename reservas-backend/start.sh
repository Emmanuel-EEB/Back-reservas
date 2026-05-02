#!/bin/sh

chown -R www-data:www-data /var/www/database /var/www/storage /var/www/bootstrap/cache
chmod -R 775 /var/www/database /var/www/storage /var/www/bootstrap/cache

php artisan config:clear
php artisan route:clear
php artisan view:clear

php artisan migrate --force

php-fpm -D
nginx -g "daemon off;"