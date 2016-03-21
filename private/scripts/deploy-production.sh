#!/bin/bash

# exit immediately if any command fails
set -e


banner()
{
  echo "*****************"
  echo "*** $1 ***"
  echo "*****************"
}

banner "creating backup"
terminus site backups create --env=live --element=all

banner "Deplying code"
terminus site deploy --env=live --updatedb --cc --note="Deployed from local env"

banner "Importing configuration"
terminus drush "cim -y" --env=live

