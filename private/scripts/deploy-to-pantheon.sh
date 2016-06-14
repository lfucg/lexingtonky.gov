#!/bin/bash

# exit immediately if any command fails
set -e

# test or live
siteenv=$1

if [ $siteenv != 'test' ] && [ $siteenv != 'live' ]
  then
  echo "first argument should be 'test' or 'live', '$siteenv' given"
fi

banner()
{
  echo "*****************"
  echo "*** $1 ***"
  echo "*****************"
}

banner "deploying to $siteenv"

if [ $siteenv == 'live' ]
  then
  banner "creating backup"
  terminus site backups create --env=$siteenv --element=all
fi

if [ $siteenv == 'test' ]
  sync='--sync-content'
fi

banner "Deplying code"
terminus site deploy --env=$siteenv $sync --updatedb --cc --note="Deployed from local env"

banner "Clear cache"
terminus site clear-cache --site=lexky-d8 --env=$siteenv

banner "Importing configuration"
terminus drush "cim -y" --env=$siteenv

