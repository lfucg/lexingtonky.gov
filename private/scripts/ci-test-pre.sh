#!/bin/bash

# fail fast
set -e

if [ "$CIRCLE_BRANCH" == "master" ]; then export SITE_ENV="dev"; else export SITE_ENV=$CIRCLE_BRANCH; fi

terminus auth login --machine-token=$MACHINE_TOKEN 2> /dev/null
git remote add pantheon $PANTHEON_REPO
git push --force pantheon $CIRCLE_BRANCH:$CIRCLE_BRANCH 2> /dev/null

if [ "$CIRCLE_BRANCH" != "master" ]; then terminus site create-env --site=$SITE_NAME --from-env=$FROM_ENV --to-env=$CIRCLE_BRANCH; fi

terminus sites aliases
terminus site clone-content --site=$SITE_NAME --from-env=live --to-env=$SITE_ENV --yes
terminus drush "updatedb -y" --site=$SITE_NAME --env=$SITE_ENV 2> /dev/null
terminus drush cr --site=$SITE_NAME --env=$SITE_ENV --yes 2> /dev/null
terminus drush "cim -y" --site=$SITE_NAME --env=$SITE_ENV 2> /dev/null

# if [ "$CIRCLE_BRANCH" == "master" ]; then
#   sed -i -e "s,http://localhost:8888,https://$DEV_USER:$DEV_PASSWORD@$SITE_ENV-$SITE_NAME.pantheonsite.io,g" behat-pantheon.yml
# else
#   sed -i -e "s,http://localhost:8888,https://$SITE_ENV-$SITE_NAME.pantheonsite.io,g" behat-pantheon.yml
# fi

# sed -i -e "s,PANTHEON_ALIAS,$SITE_NAME.$SITE_ENV,g" behat-pantheon.yml
