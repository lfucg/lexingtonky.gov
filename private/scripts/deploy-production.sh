#!/bin/bash

# exit immediately if any command fails
set -e

terminus site backups create --env=live --element=all
terminus site deploy --env=live --updatedb --cc --note="Deployed from local env"
terminus drush "cim -y" --env=live

