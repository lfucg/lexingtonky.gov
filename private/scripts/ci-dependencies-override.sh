#!/bin/bash

composer global require pantheon-systems/cli:dev-master --prefer-source --no-interaction
composer require drush/drush:8 --prefer-source --no-interaction
composer install --prefer-source --no-interaction
