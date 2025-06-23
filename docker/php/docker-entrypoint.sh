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

#php bin/console messenger:consume

exec php-fpm
