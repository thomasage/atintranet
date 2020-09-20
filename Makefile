EXEC_DOCKER_COMPOSE = docker-compose
EXEC_PHP = php7.3
EXEC_SYMFONY = symfony

.DEFAULT_GOAL := help

.PHONY: cs deploy-prod help install restart start stop test

##

help: ## Outputs this help screen
	@grep -E '(^[a-zA-Z0-9_-]+:.*?##.*$$)|(^##)' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}{printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'

##
##Deployment
##

deploy-prod: ## Deploy application to production
	@$(EXEC_PHP) vendor/bin/php-cs-fixer fix \
	&& $(EXEC_PHP) vendor/bin/dep deploy prod

##
##Local development
##

install: composer.lock docker-compose.yaml yarn.lock ## Install vendors and build Docker container
	@$(EXEC_SYMFONY) composer install
	@yarn install
	@$(EXEC_DOCKER_COMPOSE) up -d && sleep 10 && $(EXEC_DOCKER_COMPOSE) stop

prepare: ## Prepare database (run make start before)
	@$(EXEC_SYMFONY) console doctrine:database:create \
	&& $(EXEC_SYMFONY) console doctrine:migrations:migrate -n \
	&& $(EXEC_SYMFONY) console doctrine:fixtures:load -n

restart: docker-compose.yaml ## Restart local web server
	@make stop && make start

start: docker-compose.yaml ## Run local web server
	@$(EXEC_DOCKER_COMPOSE) up -d \
	&& $(EXEC_SYMFONY) serve -d \
	&& $(EXEC_SYMFONY) run -d yarn dev-server

stop: docker-compose.yaml ## Stop local web server
	@$(EXEC_SYMFONY) server:stop \
	&& $(EXEC_DOCKER_COMPOSE) stop

##
## Code check & code style
##

cs: .php_cs.dist ## Code style
	@$(EXEC_SYMFONY) run vendor/bin/php-cs-fixer fix

test: phpunit.xml.dist ## Run test suites
	@$(EXEC_SYMFONY) php bin/phpunit

##
