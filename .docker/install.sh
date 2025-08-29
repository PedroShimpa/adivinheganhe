#!/bin/bash
set -e

cd /var/www

if [ ! -d "vendor" ]; then
    composer install --no-interaction --prefer-dist
fi

php artisan migrate --force

# Rodar Octane com Swoole
php artisan octane:start --server=swoole --host=0.0.0.0 --port=8000 --watch
