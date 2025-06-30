SHELL := /bin/bash # runs commands using /bin/bash, instead of /bin/sh

start:
	docker compose up -d

stop:
	docker compose stop

app-shell:
	docker exec -it currency-exchange-app bash

app-tests-run:
	docker exec -it currency-exchange-app php bin/phpunit

app-exchange-rate-sync:
	docker exec -it currency-exchange-app php bin/console app:exchange-rate:sync

app-exchange-rate-remove:
	docker exec -it currency-exchange-app php bin/console app:exchange-rate:stop-tracking

app-exchange-rate-list:
	docker exec -it currency-exchange-app php bin/console app:exchange-rate:list

app-currency-list:
	docker exec -it currency-exchange-app php bin/console app:currency:list

app-history-list:
	docker exec -it currency-exchange-app php bin/console app:exchange-rate-history:list

worker-start:
	docker exec -it currency-exchange-worker php bin/console messenger:consume --time-limit=3600 --memory-limit=128M -vv

worker-stop:
	docker exec -it currency-exchange-worker php bin/console messenger:stop-workers

worker-shell:
	docker exec -it currency-exchange-worker bash
