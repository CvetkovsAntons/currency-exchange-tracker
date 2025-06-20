SHELL := /bin/bash # runs commands using /bin/bash, instead of /bin/sh

.PHONY: shell # ignores files with the same name

shell:
	docker exec -it currency-exchange-php bash
