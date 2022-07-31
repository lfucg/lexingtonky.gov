#!/bin/bash

set -x
terminus drush $SITE_ENV -- cache-rebuild
terminus drush $SITE_ENV -- pml
# Uninstall core search to reduce confusion in the UI.
terminus drush $SITE_ENV -- pm-uninstall search -y
terminus drush $SITE_ENV -- en -y search_api_pantheon search_api_solr search_api_page search_api
# @todo, would this test benefit from exporting/committing config?
terminus connection:set $SITE_ENV sftp
