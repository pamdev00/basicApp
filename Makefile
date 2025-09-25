COMPOSE=docker compose
## install App
init: docker-down-clear docker-pull docker-build composer-install up

## start App
up:
	$(COMPOSE) up -d  --remove-orphans
## stop App
docker-down:
	$(COMPOSE) down  --remove-orphans
## stop App and clear data
docker-down-clear:
	$(COMPOSE) down -v --remove-orphans
## pull images
docker-pull:
	$(COMPOSE) pull
## build images
docker-build:
	$(COMPOSE) build

## run composer command
composer:
	$(COMPOSE) run --rm card-php-cli composer $(filter-out $@, $(MAKECMDGOALS))
## update php packages
composer-update:
	$(COMPOSE) run --rm card-php-cli composer update

## install php packages
composer-install:
	$(COMPOSE) run --rm card-php-cli composer install




app:
	$(COMPOSE) run card-php-cli ./yii $(filter-out $@, $(MAKECMDGOALS))

test:
	$(COMPOSE) run card-php-cli ./vendor/bin/codecept run $(filter-out $@, $(MAKECMDGOALS))
test-card:
	$(COMPOSE) run card-php-cli ./vendor/bin/codecept run tests/Acceptance/Card
psalm:
	$(COMPOSE) run card-php-cli ./vendor/bin/psalm

migrate:
	$(COMPOSE) run card-php-cli ./yii migrate/up --no-interaction
