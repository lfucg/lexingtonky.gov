#!/bin/bash

# exit immediately if any command fails
set -e

# test or live
siteenv=$1

if [ $siteenv != 'test' ] && [ $siteenv != 'live' ]
  then
  echo "first argument should be 'test' or 'live', '$siteenv' given"
  exit
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
  kbox terminus site backups create --env=$siteenv --element=all
fi

if [ $siteenv == 'test' ]
  then
  sync='--sync-content'
fi

banner "Deploying code"
kbox terminus site deploy --env=$siteenv $sync --updatedb --cc --note="Deployed from local env"

banner "Clear cache"
kbox terminus site clear-cache --site=lexky-d8 --env=$siteenv

banner "Importing configuration"
kbox terminus drush "cim -y" --env=$siteenv

banner "Reset Drupal cache"
kbox terminus drush "cr -y" --env=$siteenv

