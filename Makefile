SHELL := /bin/bash # runs commands using /bin/bash, instead of /bin/sh

start:
	docker compose up -d

stop:
	docker compose stop

php-shell:
	docker exec -it currency-exchange-php bash

workers-start:
	docker exec -it currency-exchange-php php bin/console messenger:consume

workers-stop:
	docker exec -it currency-exchange-php php bin/console messenger:stop-workers
