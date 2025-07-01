#!/bin/sh
set -e

cd /var/www/symfony

echo "Waiting for app to start..."
until curl -sSf http://nginx/health-check > /dev/null; do
  sleep 1
done
echo "App is up!"

echo "Starting workers..."
php bin/console messenger:consume --time-limit=3600 --memory-limit=128M -vv
echo "Workers up!"
