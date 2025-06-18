composer install --no-interaction --prefer-dist

if ! grep -q "symfony/orm-pack" composer.json; then
    composer require symfony/orm-pack
fi

if ! grep -q "symfony/maker-bundle" composer.json; then
    composer require symfony/maker-bundle --dev
fi

php bin/console doctrine:database:create --if-not-exists

php bin/console doctrine:migrations:migrate --no-interaction
