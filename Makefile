# Load env file
ifneq ("$(wildcard .env)","")
	include .env
	export $(shell sed 's/=.*//' .env)
endif

# Init var
DOCKER_COMPOSE?=docker-compose
EXEC?=$(DOCKER_COMPOSE) exec php
EXEC_ROOT?=$(DOCKER_COMPOSE) exec -u root php
CONSOLE=bin/console
PHPCSFIXER?=$(EXEC) php -d memory_limit=1024m vendor/bin/php-cs-fixer
DOCKER_COMPOSE_OVERRIDE ?= dev
ENV ?= dev
help:
	@grep -E '(^[a-zA-Z_-]+:.*?##.*$$)|(^##)' $(firstword $(MAKEFILE_LIST)) | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'


##
## Project setup
##---------------------------------------------------------------------------

sf:																									   ## Symfony Command, example: `sf CMD="debug:router"`
	@$(EXEC) $(CONSOLE) $(CMD)

up: docker-compose.override.yml up-ci 																   ## Start project with docker-compose + Dev env

stop:                                                                                                  ## Stop docker containers
	@$(DOCKER_COMPOSE) stop

restart: stop up-ci                                                                                    ## Restart docker containers

install: docker-compose.override.yml build up vendor perm                                              ## Create and start docker containers

status:																								   ## Docker container status
	@$(DOCKER_COMPOSE) ps

uninstall: stop                                                                                        ## Remove docker containers
	@$(DOCKER_COMPOSE) rm -vf

reset: uninstall install                                                                               ## Remove and re-create docker containers

clear-cache: perm												        ## Clear + Prepare Cache (alias: c:c), you can specify the env: ENV=prod
	@$(EXEC) $(CONSOLE) cache:clear --no-warmup --env=$(ENV)
	@$(EXEC) $(CONSOLE) cache:warmup --env=$(ENV)

c\:c: clear-cache

shell:                                                                                                 ## Run app container in interactive mode
	@$(EXEC) /bin/bash

clear: perm                                                                                            ## Remove all the cache, the logs, the sessions and the built assets
	@$(EXEC_ROOT) rm -rf var/cache/* var/log/* public/build
	@$(EXEC_ROOT) rm -f var/.php_cs.cache

clean: clear                                                                                           ## Clear and remove dependencies
	@$(EXEC_ROOT) rm -rf vendor

composer-update:										 ## Composer update. You can specified package, example: `make api-composer-update CMD="twig/twig"`
	@$(EXEC) composer update $(CMD)

##
## Database
##---------------------------------------------------------------------------

db-diff: vendor                                                                                        ## Generate a migration by comparing your current database to your mapping information
	@$(EXEC) $(CONSOLE) doctrine:migration:diff

db-migrate: vendor                                                                                     ## Migrate database schema to the latest available version
	@$(EXEC) $(CONSOLE) doctrine:migration:migrate -n

db-rollback: vendor                                                                                    ## Rollback the latest executed migration
	@$(EXEC) $(CONSOLE) doctrine:migration:migrate prev -n

db-fixtures: vendor                                                                                    ## Apply doctrine fixtures
	@$(EXEC) $(CONSOLE) doctrine:fixtures:load -n

db-validate: vendor                                                                                    ## Check the ORM mapping
	@$(EXEC) $(CONSOLE) doctrine:schema:validate


# ##
# ## Assets
# ##---------------------------------------------------------------------------

#watch: node_modules                                                                                    ## Watch the assets and build their development version on change
#	@$(EXEC) yarn watch
#
#assets: node_modules                                                                                   ## Build the development version of the assets
#	@$(EXEC) yarn dev
#
#assets-build: node_modules                                                                              ## Build the production version of the assets
#	@$(EXEC) yarn build

##
## Tests
##---------------------------------------------------------------------------

tests:                                                                                                 ## Run all the PHP tests
	@$(EXEC) bin/phpunit

lint: lint-symfony php-cs                                                                              ## Run lint on Twig, YAML, PHP and Javascript files

lint-symfony: lint-yaml lint-twig lint-xliff                                                           ## Lint Symfony (Twig and YAML) files

lint-yaml:                                                                                             ## Lint YAML files
	@$(EXEC) $(CONSOLE) lint:yaml config

lint-twig:                                                                                             ## Lint Twig files
	@$(EXEC) $(CONSOLE) lint:twig templates

lint-xliff:                                                                                             ## Lint Translation files
	@$(EXEC) $(CONSOLE) lint:xliff translations

php-cs: vendor                                                                                         ## Lint PHP code
	@$(PHPCSFIXER) fix --diff --dry-run --no-interaction -v

php-cs-fix: vendor                                                                                     ## Lint and fix PHP code to follow the convention
	@$(PHPCSFIXER) fix

security-check: vendor                                                                                 ## Check for vulnerable dependencies
	@$(EXEC) vendor/bin/security-checker security:check

test-schema: vendor                                                                                    ## Test the doctrine Schema
	@$(EXEC) $(CONSOLE) doctrine:schema:validate --skip-sync -vvv --no-interaction

test-all: lint test-schema security-check tests                                                        ## Lint all, check vulnerable dependencies, run PHP tests

##


# Internal rules
build:
	@$(DOCKER_COMPOSE) pull --ignore-pull-failures
	@$(DOCKER_COMPOSE) build --force-rm

up-ci:
	@$(DOCKER_COMPOSE) up -d --remove-orphans

perm:
	@$(EXEC_ROOT) chmod -R 775 var public/uploads
	@$(EXEC_ROOT) chgrp -R www-data var/ public/uploads
	@$(EXEC_ROOT) chmod +x bin/* vendor/bin/*

#docker-compose.override.yml:
#ifneq ($(wildcard docker-compose.override.yml),docker-compose.override.yml)
#	@echo docker-compose.override.yml do not exists, copy docker-compose.override.yml.dist to create it, and fill it.
#	exit 1
#endif

define echo_text
	echo -e '\e[1;$(2)m$(1)\e[0m'
endef

docker-compose.override.yml: docker-compose.$(DOCKER_COMPOSE_OVERRIDE).yml
	@test -f docker-compose.override.yml \
		&& $(call echo_text,/!\ docker-compose.$(DOCKER_COMPOSE_OVERRIDE).yml might have been modified - remove docker-compose.override.yml to be up-to-date,31) \
		|| ( echo "Copy docker-compose.override.yml from docker-compose.$(DOCKER_COMPOSE_OVERRIDE).yml"; cp docker-compose.$(DOCKER_COMPOSE_OVERRIDE).yml docker-compose.override.yml )

# Rules from files
vendor:
	@$(EXEC) composer install -n


#node_modules: yarn.lock
#	@$(EXEC) yarn install
#
#yarn.lock: package.json
#	@echo yarn.lock is not up to date.
