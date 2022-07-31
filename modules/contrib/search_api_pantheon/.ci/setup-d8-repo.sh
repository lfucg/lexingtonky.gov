#!/bin/bash

# Bring the code down to Circle so that modules can be added via composer.
git clone $(terminus connection:info $SITE_ENV --field=git_url) drupal8
cd drupal8
git checkout -b $TERMINUS_ENV

# Composer is erroring when there is an empty require-dev attribute.
# So put something in it.
composer require --dev drush-ops/behat-drush-endpoint
# Tell Composer where to find packages.
composer config repositories.search_api_pantheon vcs git@github.com:pantheon-systems/search_api_pantheon.git
composer require drupal/search_api_pantheon:"${GIT_REF}"
composer require drupal/search_api_page:1.x-dev

# Make sure submodules are not committed.
rm -rf modules/contrib/search_api_solr/.git/
rm -rf modules/contrib/search_api/.git/
rm -rf modules/contrib/search_api_page/.git/
rm -rf modules/contrib/search_api_pantheon/.git/
rm -rf vendor/solarium/solarium/.git/

# Make a git commit
git add .
git commit -m 'Result of build step'
git push --set-upstream origin $TERMINUS_ENV
