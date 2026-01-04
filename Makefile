help:			## Display help information.
	@fgrep -h "##" $(MAKEFILE_LIST) | fgrep -v fgrep | sed -e 's/\\$$//' | sed -e 's/##//'

start:			## Start services
	docker compose -f tests/docker/docker-compose.yml up -d

test:			## Run tests. Params: {{ v=8.1 }}.
	make build
	PHP_VERSION=$(filter-out $@,$(v)) docker compose -f tests/docker/docker-compose.yml build --pull yii2-redis-php
	PHP_VERSION=$(filter-out $@,$(v)) docker compose -f tests/docker/docker-compose.yml up -d
	PHP_VERSION=$(filter-out $@,$(v)) docker compose -f tests/docker/docker-compose.yml exec yii2-redis-php sh -c "php -v && composer update && vendor/bin/phpunit --coverage-clover=coverage.xml"

build:			## Build an image from a docker-compose file. Params: {{ v=8.1 }}.
	PHP_VERSION=$(filter-out $@,$(v)) docker compose -f tests/docker/docker-compose.yml up -d --build

down:			## Stop and remove containers, networks
	docker compose -f tests/docker/docker-compose.yml down

sh:			## Enter the container with the application
	docker exec -it docker-yii2-redis-php-1 bash
