# Development

The following instructions to set up a local development environment for the city's Drupal site. (NOTE: With Kalabox no longer being supported, the instructions below will help get a local environment set up with the new version of Kalabox which is called [Lando](https://docs.devwithlando.io/))

## Install the site locally

(UPDATED 12/23/2017)

Install Lando and authenticate with Pantheon to pull down the site code/files/db. Follow instructions to pull Github code from Pantheon OR do a `lando init pantheon` with an empty directory.

[Getting Started with Lando Docs](https://docs.devwithlando.io/started.html)

From start to finish in terminal:
```
$ lando init pantheon
$ lando start
$ terminus auth:login --machine-token=[token_from_pantheon]
$ lando pull
```
`lando pull`: You will need a machine token from Pantheon user account page. The output will ask where to pull the code, database, and site files from.

```
$ lando restart
$ lando drush user-login --uri=<<LOCALHOST PORT>>
```

##### For updating the Solr configuration and getting it to work locally

Anytime you need to get the Solr server to connect you will:
* `$ lando rebuild -s`
* Go to Home Administration > Configuration > Search and metadata > Search API > Server > Edit then click save to resave it
* Reindex at Administration > Configuration > Search and metadata > Search API > Pantheon Index

The site should now be served locally on one of the ports listed in the CLI.

For information on all running services: `$ lando info`

### Import/export of configuration changes

As you make changes to the site through the UI, you'll want to export your configuration changes:

`lando drush config-export -y`

And to set the local database to the configuration stored in git:

`lando drush config-import -y`

### Theme development

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

## Log in to administration backend

* Test on your local machine.
  * Download data (described below)
  * Login via `lando drush user-login --uri=localhost:8888` (Use the uri from lando's current localhost server (e.g. --uri=localhost:32750))

## Getting data

* `lando pull`
* Follow prompts for code, database, and files.

## Running tests locally

[See TESTING.md](TESTING.md)

## Updating modules and deploying

[See DEPLOYMENT.md](DEPLOYMENT.md)

##### For testing/merging CLU branches

* Checkout master, make sure master is up to date & run lando drush cim
* Make sure the clu branch is rebased on the current master
* Run lando composer install
* Run lando drush updb
* Run lando drush cex
* Check that the site is working properly
