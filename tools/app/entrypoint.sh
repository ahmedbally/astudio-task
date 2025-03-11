#!/bin/bash

sleep 5

# Run migrations and seed the database
php artisan migrate --seed

# Run queue worker in the background
php artisan queue:work --tries=3 &

# Start PHP-FPM
php-fpm
