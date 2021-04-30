# You need setup here: ALIAS, BUILD_IMAGE_CLI, BUILD_IMAGE_FPM
ALIAS              = mmalessa-process-manager
BASE_IMAGE         ?= php:7.4.13-cli
TARGET_IMAGE       = $(ALIAS)-package
####

.DEFAULT_GOAL      = help
PLATFORM          ?= $(shell uname -s)
EXEC_PHP           = php
COMPOSER           = composer
VERSION           ?= `git describe --tags --always --dirty`
DOCKER_GATEWAY    ?= $(shell if [ 'Linux' = "${PLATFORM}" ]; then ip addr show docker0 | awk '$$1 == "inet" {print $$2}' | grep -oE '[0-9]+\.[0-9]+\.[0-9]+\.[0-9]+'; fi)
COMPOSE            = docker-compose
DEV_PATH          ?= docker/dev
DEV_DOCKERFILE    ?= $(DEV_PATH)/config/php/Dockerfile
TAG               ?= $(VERSION)
DEVELOPER_UID     ?= $(shell id -u)

#-----------------------------------------------------------------------------------------------------------------------
#-----------------------------------------------------------------------------------------------------------------------
ARG := $(word 2, $(MAKECMDGOALS))
%:
	@:
test-run:
	@echo $(PLATFORM)
help:
	@echo -e '\033[1m make [TARGET] \033[0m'
	@grep -E '(^[a-zA-Z0-9_-]+:.*?##.*$$)|(^##)' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}{printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'
	@echo && $(MAKE) -s env-info
alias: ## auto update aliases in docker files (.env, docker-compose.yaml)
	@sed -i 's/!{ALIAS}/'"$(shell sed -n 's/^ALIAS *=//p' Makefile | xargs)"'/g' ./.docker/.env
#-----------------------------------------------------------------------------------------------------------------------
#-----------------------------------------------------------------------------------------------------------------------

## -- Docker -----------------------------------------------------------------------------------------------------------
build: ## Build dev image
	@docker build -t $(TARGET_IMAGE)                         \
		--build-arg BASE_IMAGE=$(BASE_IMAGE)        \
		--build-arg DEVELOPER_UID=$(DEVELOPER_UID)  \
		-f $(DEV_DOCKERFILE) .

up: ## Start the project docker containers
	cd ./docker && $(COMPOSE) up -d

down: ## Remove the docker containers
	@cd ./docker && $(COMPOSE) down

stop: ## Stop the docker containers
	@cd ./docker && $(COMPOSE) stop

## -- Project ----------------------------------------------------------------------------------------------------------
console: ## Enter into application container
	@if [ "${ARG}" = 'root' ] || [ "${ARG}" = 'r' ]; then docker exec -it -u root $(TARGET_IMAGE) bash; fi
	@if [ "${ARG}" = '' ] || [ "${ARG}" = 'developer' ]; then docker exec -it $(TARGET_IMAGE) bash; fi

version: ## Show project version
	@echo version: $(VERSION)

