FROM php:8.4-fpm

RUN apt-get update && apt-get install -y \
    git unzip curl libpq-dev libzip-dev zip \
    netcat-openbsd \
    && docker-php-ext-install pdo pdo_pgsql zip

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/symfony

COPY . .

COPY docker/php/docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh

ENTRYPOINT ["docker-entrypoint.sh"]
