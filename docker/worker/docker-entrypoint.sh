#!/bin/bash
set -e

cd /var/www/symfony

if [ ! -d "vendor" ]; then
    echo "Installing composer dependencies..."
    composer install --no-interaction --prefer-dist
fi

echo "Starting workers..."
docker exec -it currency-exchange-worker php bin/console messenger:consume --time-limit=3600 --memory-limit=128M
echo "Workers up!"

exec php-fpm
