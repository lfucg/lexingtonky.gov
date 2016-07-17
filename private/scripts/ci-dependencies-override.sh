#!/bin/bash

# add dev dependencies
composer global require pantheon-systems/terminus:dev-master --no-interaction
composer require drush/drush:"^8.1" --no-interaction
composer require drupal/drupal-extension:"3.1.5" --no-interaction
composer require jarnaiz/behat-junit-formatter:"^1.3" --no-interaction
