#!/bin/bash

export BEHAT_PARAMS='{"extensions" : {"Behat\\MinkExtension" : {"base_url" : "https://'$TERMINUS_ENV'-'$TERMINUS_SITE'.pantheonsite.io/"}}}'
./vendor/bin/behat --config=tests/behat/behat-pantheon.yml --strict --stop-on-failure
