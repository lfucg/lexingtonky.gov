#!/bin/bash

set -e

# . clear_solr_index.sh

lando drush sapi-r

lando drush sapi-i --batch-size 200
