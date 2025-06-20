SHELL := /bin/bash # runs commands using /bin/bash, instead of /bin/sh

.PHONY: php-shell # ignores files with the same name

start:
	docker exec up -d

setup:
	bin/setup.sh

php-shell:
	docker exec -it currency-exchange-php bash
