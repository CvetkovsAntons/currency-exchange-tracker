SHELL := /bin/bash # runs commands using /bin/bash, instead of /bin/sh

start:
	docker compose up -d

stop:
	docker compose stop

app-shell:
	docker exec -it currency-exchange-app bash

app-worker-start:
	docker exec -it currency-exchange-worker php bin/console messenger:consume --time-limit=3600 --memory-limit=128M

app-worker-stop:
	docker exec -it currency-exchange-worker php bin/console messenger:stop-workers

exchange-rate-sync:
	docker exec -it currency-exchange-app php bin/console app:exchange-rate:sync

exchange-rate-remove:
	docker exec -it currency-exchange-app php bin/console app:exchange-rate:stop-tracking

tests-run:
	docker exec -it currency-exchange-app php bin/phpunit
