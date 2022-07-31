#!make
DEPENDENCIES=""
BRANCH_NAME=$(shell git rev-parse --abbrev-ref HEAD)
PWD=$(shell pwd)
include .env
export $(shell sed 's/=.*//' .env)

# Start container and build Drupal 8 locally
build-local-8:
	docker run --rm --name drupalci_${PROJECT_NAME} \
	    -v ${PWD}/:/var/www/html/web/modules/contrib/${PROJECT_NAME} \
	    -v ~/artifacts:/artifacts \
	    -p ${PROJECT_PORT}:80 \
	    -d marcelovani/drupalci:8-apache-interactive
	make build-local

# Start container and build Drupal 9 locally
build-local-9:
	docker run --rm --name drupalci_${PROJECT_NAME} \
	    -v ${PWD}/:/var/www/html/web/modules/contrib/${PROJECT_NAME} \
	    -v ~/artifacts:/artifacts \
	    -p ${PROJECT_PORT}:80 \
	    -d marcelovani/drupalci:9-apache-interactive
	make build-local

build-local:
	docker exec -i drupalci_${PROJECT_NAME} bash -c "composer require ${DEPENDENCIES}"
	docker exec -i drupalci_${PROJECT_NAME} bash -c "sudo -u www-data php web/core/scripts/drupal install standard"

# Test local build
test-local:
	docker exec -it drupalci_${PROJECT_NAME} bash -c '\
	    sudo -u www-data php web/core/scripts/run-tests.sh \
	    --php /usr/local/bin/php \
	    --verbose \
	    --keep-results \
	    --color \
	    --concurrency "32" \
	    --repeat "1" \
	    --types "Simpletest,PHPUnit-Unit,PHPUnit-Kernel,PHPUnit-Functional" \
	    --sqlite sites/default/files/.ht.sqlite \
	    --url http://localhost \
	    --directory "modules/contrib/${PROJECT_NAME}"'

# Test in non-interactive mode
test-8:
	docker run --name drupalci_${PROJECT_NAME} \
	    -v ~/artifacts:/artifacts \
	    --rm marcelovani/drupalci:8-apache \
	    --project ${PROJECT_NAME} \
	    --version dev-1.x \
	    --dependencies ${DEPENDENCIES}

# Test in non-interactive mode
test-9:
	docker run --name drupalci_${PROJECT_NAME} \
	    -v ~/artifacts:/artifacts \
	    --rm marcelovani/drupalci:9-apache \
	    --project ${PROJECT_NAME} \
	    --version dev-1.x \
	    --dependencies ${DEPENDENCIES}

open:
	open "http://$(PROJECT_BASE_URL):${PROJECT_PORT}"

stop:
	docker stop drupalci_${PROJECT_NAME}

stop-all-containers:
	ids=$$(docker ps -a -q) && if [ "$${ids}" != "" ]; then docker stop $${ids}; fi

in:
	docker exec -it drupalci_${PROJECT_NAME} bash
