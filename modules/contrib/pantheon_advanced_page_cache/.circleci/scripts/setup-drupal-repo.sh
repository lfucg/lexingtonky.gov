#!/bin/bash
set -e
export TERMINUS_ENV=$CIRCLE_BUILD_NUM

# Bring the code down to Circle so that modules can be added via composer.
git clone $(terminus connection:info ${TERMINUS_SITE}.dev --field=git_url) --branch $TERMINUS_ENV drupal-site
cd drupal-site

# requiring other modules below was throwing an error if this dependency was not updated first.
# I think because the composer.lock file for the site has dev-master as the version for this
# dependency. But the CI process calling this file runs against a different branch name thanks to the
# git clone command above.
composer update "pantheon-upstreams/upstream-configuration"

composer -- config repositories.papc vcs git@github.com:pantheon-systems/pantheon_advanced_page_cache.git
# Composer require the given commit of this module
composer -- require drupal/views_custom_cache_tag "drupal/pantheon_advanced_page_cache:dev-${CIRCLE_BRANCH}#${CIRCLE_SHA1}"

# Don't commit a submodule
rm -rf web/modules/contrib/pantheon_advanced_page_cache/.git/

# Make a git commit
git add .
git commit -m 'Result of build step'
git push --set-upstream origin $TERMINUS_ENV
