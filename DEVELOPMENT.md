# Development

The following instructions to set up a local development environment for the city's Drupal site.

## Install the site locally

Install Kalabox and authenticate with Pantheon to pull down the site code/files/db

### Import/export of configuration changes

As you make changes to the site through the UI, you'll want to export your configuration changes:

`kbox drush config-export -y`

And to set the local database to the configuration stored in git:

`kbox drush config-import -y`

### Theme development

install npm

```
cd themes/custom/lex
npm install
# infinite loop to re-run gulp if it dies on invalid Sass syntax
while true; do node node_modules/gulp/bin/gulp.js; sleep 2; done
```

### Configure local settings

copy sites/example.settings.local.php sites/default/settings.local.php

append to the new file: settings.local.php

```php
$settings['trusted_host_patterns'][] = '^localhost$';

/**
 * Enable access to rebuild.php.
 *
 * This setting can be enabled to allow Drupal's php and database cached
 * storage to be cleared via the rebuild.php page. Access to this page can also
 * be gained by generating a query string from rebuild_token_calculator.sh and
 * using these parameters in a request to rebuild.php.
 */
$settings['rebuild_access'] = FALSE;

$settings['hash_salt'] = 'somethingunique';
```

## Debugging issues reported by contributors

* Test on your local machine.
  * Download data (described below)
  * Login via `drush user-login --uri=localhost:8888`
* Or: create a multi-dev environment with content cloned from live.
* Go to the contributor's page and 'Masquerade as jsmith` to reproduce the issue.

## Getting data

* Install [terminus](https://github.com/pantheon-systems/terminus)
* Get machine token [from Pantheon](https://dashboard.pantheon.io/users/#account/)
* Run `terminus auth login --machine-token=<copied-from-pantheon>`
* Download DB `terminus site backups get --site=lexky-d8 --element=database --env=live --to=. --latest`

## Running tests locally

[See TESTING.md](TESTING.md)

## Updating modules and deploying

[See DEPLOYMENT.md](DEPLOYMENT.md)

