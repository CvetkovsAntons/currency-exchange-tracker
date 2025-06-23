#!/bin/bash
set -e

cd /var/www/symfony

echo "Waiting for database..."
until nc -z postgres 5432; do
  sleep 1
done
echo "Database is up!"

if [ ! -d "vendor" ]; then
    echo "Installing composer dependencies..."
    composer install --no-interaction --prefer-dist
fi


echo "Running migrations..."
php bin/console doctrine:database:create --if-not-exists
php bin/console doctrine:migrations:migrate --no-interaction

echo "Starting workers..."
php bin/console messenger:consume > var/log/worker.log 2>&1 &
echo "Workers started successfully..."

exec php-fpm
