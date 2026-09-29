#!/bin/bash

set -e

echo "Starting PHP-FPM..."
php-fpm -D

echo "Running Laravel migrations..."
php artisan migrate --force

echo "Caching Laravel configuration..."
php artisan config:cache

echo "Caching Laravel routes..."
php artisan route:cache

echo "Starting Nginx..."
nginx -g "daemon off;"
