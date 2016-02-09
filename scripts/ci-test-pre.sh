#!/bin/bash

if [ "$CIRCLE_BRANCH" == "master" ]; then export SITE_ENV="dev"; else export SITE_ENV=$CIRCLE_BRANCH; fi

terminus auth login --machine-token=$MACHINE_TOKEN 2> /dev/null
git remote add pantheon $PANTHEON_REPO
git push --force pantheon $CIRCLE_BRANCH:$CIRCLE_BRANCH 2> /dev/null

if [ "$CIRCLE_BRANCH" != "master" ]; then terminus site create-env --site=$SITE_NAME --from-env=$FROM_ENV --to-env=$CIRCLE_BRANCH; fi

terminus sites aliases
terminus site clone-content --site=$SITE_NAME --from-env=live --to-env=$SITE_ENV --yes
terminus drush "cim -y" --site=$SITE_NAME --env=$SITE_ENV 2> /dev/null
terminus drush "pm-enable -y devel" --site=$SITE_NAME --env=$SITE_ENV
terminus drush "config-set -y system.mail interface.default devel_mail_log" --site=$SITE_NAME --env=$SITE_ENV
terminus drush cr --site=$SITE_NAME --env=$SITE_ENV --yes 2> /dev/null

sed -i -e "s,http://localhost:8888,https://$SITE_ENV-$SITE_NAME.pantheon.io,g" behat-pantheon.yml
sed -i -e "s,PANTHEON_ALIAS,$SITE_NAME.$SITE_ENV,g" behat-pantheon.yml
