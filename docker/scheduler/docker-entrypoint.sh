#!/bin/bash
set -e

cd /var/www/symfony

php bin/console messenger:consume

exec php-fpm
