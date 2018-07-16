DB_NAME ?= anahita## mysql db name 
DB_HOST ?= mysql.example.com## mysql db host
DB_PORT ?= 3306## mysql port
DB_USER ?= root## mysql user
DB_PASSWORD ?= password## mysql password
DB_PREFIX ?= an_## mysql tables prefix



.PHONY: help

help: defaults
	@echo "Commands:"
	@echo "------------------------------"
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'
	@echo

defaults:
	@echo 
	@echo "Environment defaults:"
	@echo "------------------------------"
	@grep -E '^[a-zA-Z_-]+\s*\?=\s*.*$$' $(MAKEFILE_LIST) | sort |  awk 'BEGIN {FS = "\\?= *|## "}; {printf "\033[33m%-30s\033[0m%-45s \033[37m %-30s\033[0m\n", $$1, $$2, $$3}'
	@echo


build: ## build image
	@rm -rf www
	@docker rm --force $(DB_HOST) || true
	@docker run --name $(DB_HOST) -e MYSQL_ROOT_PASSWORD=$(DB_PASSWORD) -d mysql:5.6
	@docker build -t anahita-build -f Dockerfile.build .
	@docker run -v $$PWD:/app --link $(DB_HOST):$(DB_HOST) -it anahita-build:latest php /composer/composer.phar update
	@docker run -v $$PWD:/app --link $(DB_HOST):$(DB_HOST) -it anahita-build:latest php anahita site:init \
		--database-name $(DB_NAME) --database-user $(DB_USER) --database-password $(DB_PASSWORD) --database-host $(DB_HOST) \
		--database-port $(DB_PORT) --database-prefix $(DB_PREFIX) --drop-database
	@docker build -t anahita-run -f Dockerfile.run .

run: ## run image
	@docker run --link $(DB_HOST):$(DB_HOST) -it -p 80:80 anahita-run:latest


