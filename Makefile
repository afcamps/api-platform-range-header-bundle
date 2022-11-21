.EXPORT_ALL_VARIABLES:
.DEFAULT_GOAL := help
.PHONY: shell

COLOR_RESET = \033[0m
COLOR_ERROR = \033[31m
COLOR_INFO = \033[32m
COLOR_COMMENT = \033[33m
COLOR_TITLE_BLOCK = \033[0;44m\033[37m

DOCKER_COMPOSE ?= docker-compose -f docker-compose.yaml
DOCKER_EXEC ?= exec
CONTAINER = "app"
UID = $(shell id -u)
APP_ENV ?= dev
APP_PORT ?= 8080

## Initialize environment & application
init:
	@make docker-up
	@make composer
	@make database

## Spawn shell in app's container
shell:
	@${DOCKER_COMPOSE}  ${DOCKER_EXEC} -u ${UID} ${CONTAINER} bash

## Remove app's cache
cache-clear:
	@${DOCKER_COMPOSE}  ${DOCKER_EXEC} -e XDEBUG_MODE=off -u ${UID} ${CONTAINER} rm -rf var/cache/*
	@${DOCKER_COMPOSE}  ${DOCKER_EXEC} -e XDEBUG_MODE=off -u ${UID} ${CONTAINER} bin/console cache:warmup

## Start docker environment
docker-up:
	@${DOCKER_COMPOSE}  up -d --build --always-recreate-deps --remove-orphans

## Stop docker containers
docker-stop:
	@${DOCKER_COMPOSE}  stop

## Down docker containers
docker-down:
	@${DOCKER_COMPOSE}  down -v

## Restart docker environment
docker-restart:
	@${DOCKER_COMPOSE}  restart

## Logs docker containers
docker-logs:
	@${DOCKER_COMPOSE}  logs -f ${service}

## Rebuild docker containers
docker-build:
	@${DOCKER_COMPOSE}  build

## Composer (default arg="install --no-progress")
composer: arg = install --no-progress
composer:
	@${DOCKER_COMPOSE}  ${DOCKER_EXEC} -e XDEBUG_MODE=off -u ${UID} ${CONTAINER} composer ${arg}

## Initialize database
database: clear-database create-database database-fixtures

## Clear database
clear-database:
	@${DOCKER_COMPOSE}  ${DOCKER_EXEC} -e XDEBUG_MODE=off -u ${UID} -e APP_ENV=${APP_ENV} ${CONTAINER} php bin/console doctrine:database:drop --if-exists --force
	@${DOCKER_COMPOSE}  ${DOCKER_EXEC} -e XDEBUG_MODE=off -u ${UID} -e APP_ENV=${APP_ENV} ${CONTAINER} php bin/console doctrine:database:create

## Run create schema
create-database:
	@${DOCKER_COMPOSE}  ${DOCKER_EXEC} -e XDEBUG_MODE=off -u ${UID} -e APP_ENV=${APP_ENV} ${CONTAINER} php bin/console doctrine:schema:create --no-interaction --quiet

## Load database fixtures
database-fixtures:
	@${DOCKER_COMPOSE}  ${DOCKER_EXEC} -e XDEBUG_MODE=off -u ${UID} -e APP_ENV=${APP_ENV} ${CONTAINER} bin/console doctrine:fixtures:load -n --ansi

## Disable xdebug
xdebug-disable:
	@XDEBUG_MODE=off ${DOCKER_COMPOSE} up -d ${CONTAINER}

## Enable xdebug
xdebug-enable:
	@XDEBUG_MODE=develop,debug ${DOCKER_COMPOSE} up -d ${CONTAINER}

## List available commands
help:
	@printf "${COLOR_TITLE_BLOCK}${PROJECT} Makefile${COLOR_RESET}\n"
	@printf "\n"
	@printf "${COLOR_COMMENT}Usage:${COLOR_RESET}\n"
	@printf " make [target] [arg=\"val\"...]\n\n"
	@printf "${COLOR_COMMENT}Available targets:${COLOR_RESET}\n"
	@awk '/^[a-zA-Z\-\_0-9\@]+:/ { \
		helpLine = match(lastLine, /^## (.*)/); \
		helpCommand = substr($$1, 0, index($$1, ":")); \
		helpMessage = substr(lastLine, RSTART + 3, RLENGTH); \
		printf " ${COLOR_INFO}%-20s${COLOR_RESET} %s\n", helpCommand, helpMessage; \
	} \
	{ lastLine = $$0 }' $(MAKEFILE_LIST)
