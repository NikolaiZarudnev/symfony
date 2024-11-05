THIS_FILE := $(lastword $(MAKEFILE_LIST))
.PHONY: help build up start down destroy stop restart logs ps symfony create-db migration migrate npm-install npm-watch
help:
	make -pRrq  -f $(THIS_FILE) : 2>/dev/null | awk -v RS= -F: '/^# File/,/^# Finished Make data base/ {if ($$1 !~ "^[#.]") {print $$1}}' | sort | egrep -v -e '^[^[:alnum:]]' -e '^$@$$'

build:
	docker-compose build $(c)

up:
	docker-compose up -d $(c)

start:
	docker-compose start $(c)

down:
	docker-compose down $(c)

destroy:
	docker-compose down -v $(c)

stop:
	docker-compose stop $(c)

restart:
	docker-compose stop $(c)
	docker-compose up -d $(c)

logs:
	docker-compose logs --tail=100 -f $(c)

ps:
	docker-compose ps

bash:
	docker-compose exec php bash

composer-update:
	docker-compose exec php composer update

symfony:
	docker-compose exec php symfony console $(c)

cache-clear:
	docker-compose exec php symfony console cache:clear

db-create:
	docker-compose exec php symfony console doctrine:database:create

db-drop:
	docker-compose exec php symfony console doctrine:database:drop --force

migration:
	docker-compose exec php symfony console make:migration

migrate:
	docker-compose exec php symfony console doctrine:migration:migrate

fixtures:
	docker-compose exec php symfony console doctrine:fixtures:load

nodejs-bash:
	docker-compose exec nodejs bash

nodejs-exec:
	docker-compose exec nodejs $(c)

nodejs-run:
	docker-compose run nodejs $(c)

npm-install:
	docker-compose exec nodejs npm install $(c)

npm-build:
	docker-compose exec nodejs npm run build

npm-watch:
	docker-compose exec nodejs npm run watch
