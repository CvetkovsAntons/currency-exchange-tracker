SHELL := /bin/bash # runs commands using /bin/bash, instead of /bin/sh

.PHONY: php-shell # ignores files with the same name

start:
	docker corpose up -d

stop:
	docker compose down

setup:
	bin/setup.sh

php-shell:
	docker exec -it currency-exchange-php bash

workers-start:
	php bin/console messenger:consume

workers-stop:
	php bin/console messenger:stop-workers
