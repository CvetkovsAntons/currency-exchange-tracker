#!/bin/sh
set -e

cd /var/www/symfony

echo "Waiting for database..."
until nc -z postgres 5432; do
  sleep 1
done
echo "Database is up!"

if [ ! -f "vendor/autoload_runtime.php" ]; then
    echo "Installing composer dependencies..."
    composer install --no-interaction --prefer-dist
fi

echo "Running migrations for default env..."
php bin/console doctrine:database:create --if-not-exists
php bin/console doctrine:migrations:migrate --no-interaction

echo "Running migrations for test env..."
php bin/console doctrine:database:create --if-not-exists --env=test
php bin/console doctrine:migrations:migrate --no-interaction --env=test

exec php-fpm
