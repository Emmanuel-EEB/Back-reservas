#!/bin/sh

php artisan config:clear
php artisan route:clear
php artisan view:clear

php artisan migrate --force

php-fpm -D
nginx -g "daemon off;"