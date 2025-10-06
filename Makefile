DOCKER_COMPOSE  = docker compose

EXEC_DB         = $(DOCKER_COMPOSE) exec database
EXEC_PHP        = $(DOCKER_COMPOSE) exec php

SYMFONY_CONSOLE = $(EXEC_PHP) bin/console
COMPOSER        = $(EXEC_PHP) composer

DB_NAME = corahnrin
DB_USER = root
DB_PWD = corahnrin

CURRENT_DATE = `date "+%Y-%m-%d_%H-%M-%S"`

##
## Project
## -------
##

.DEFAULT_GOAL := help
help: ## Show this help message
	@grep -E '(^[a-zA-Z_-]+:.*?##.*$$)|(^##)' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[32m%-20s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'
.PHONY: help

install: ## Install and start the project
install: config.php build start vendor db
.PHONY: install

config.php:
	cp config.php.dist config.php

build: ## Build the Docker images
	@$(DOCKER_COMPOSE) pull --include-deps
	@$(DOCKER_COMPOSE) build
.PHONY: build

start: ## Start all containers and the PHP server
	@$(DOCKER_COMPOSE) up -d --remove-orphans --no-recreate
.PHONY: start

stop: ## Stop all containers and the PHP server
	@$(DOCKER_COMPOSE) stop
.PHONY: stop

restart: ## Restart the containers & the PHP server
	@$(MAKE) stop
	@$(MAKE) start
.PHONY: restart

kill: ## Stop all containers
	$(DOCKER_COMPOSE) kill
	$(DOCKER_COMPOSE) down --volumes --remove-orphans
.PHONY: kill

reset: ## Stop and start a fresh install of the project
reset: kill install
.PHONY: reset

clean: ## Stop the project and remove generated files and configuration
clean: kill
	rm -rf vendor tmp/* logs/404/*.log logs/cache_sql/*.log logs/error_tracking/*.log logs/exectime/*.log logs/referer/*.log logs/sql/*.log
.PHONY: clean

##
## Tools
## -----
##

cc: ## Clear local cache
	rm -rf tmp/*
.PHONY: cc

db: ## Reset the development database
db: wait-for-db
	$(EXEC_DB) bash /app/install_database.bash -f /app/install.sql
.PHONY: db

prod-db: ## Uses a prod dump as local database
prod-db: wait-for-db
	$(EXEC_DB) bash /app/install_database.bash -f dump.sql
.PHONY: prod-db

vendor: ## Install PHP vendors
	$(COMPOSER) install
.PHONY: vendor

wait-for-db:
	@echo " Waiting for database..."
	@for i in {1..5}; do $(EXEC_DB) mysql -u$(DB_USER) -p$(DB_PWD) -e "SELECT 1;" > /dev/null 2>&1 && sleep 1 || echo " Unavailable..." ; done;
.PHONY: wait-for-db
