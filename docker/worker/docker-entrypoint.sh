#!/bin/sh
set -e

cd /var/www/symfony

if [ ! -f "vendor/autoload_runtime.php" ]; then
    echo "Installing composer dependencies..."
    composer install --no-interaction --prefer-dist
fi

echo "Starting workers..."
php bin/console messenger:consume --time-limit=3600 --memory-limit=128M -vv
echo "Workers up!"
