[![Circle CI](https://circleci.com/gh/lfucg/lexingtonky.gov/tree/master.svg?style=svg)](https://circleci.com/gh/lfucg/lexingtonky.gov/tree/master)
Visual regression testing via [Browserstack Automate](browserstack.com/automate) (currently in alpha, housed in [separate repo](https://github.com/eeeschwartz/shoov-tutorial))

# Lexington, KY city site

The upcoming site for the City of Lexington, currently in pilot phase. It's built on [Drupal 8](https://www.drupal.org/8) and uses the draft [US Web Design Standards](https://playbook.cio.gov/designstandards/).

## Installation

Use the following instructions to set up a local development environment for the city's Drupal site.

An [example walkthrough of install process](http://erikschwartz.net/2015-11-16-install-pantheon-drupal-8-mamp) on a Mac.

### Download codebase

```
git clone git@github.com:lfucg/lexingtonky.gov.git
cd lexingtonky.gov
```

### Download database from pantheon

Add to .ssh/config

```
Host pantheon
  HostName codeserver.dev.PANTHEONID.drush.in
  Port 2222
  User codeserver.dev.PANTHEONID
  IdentityFile ~/.ssh/your-identity-file.id_rsa
```

```
mysqldump -u pantheon -pPASSWORD -h pantheon-db-server -P pantheon-port pantheon --add-drop-table > dev.lexingtonky.gov.sql
mysql -u USER -pPASSWORD your-local-db < dev.lexingtonky.gov.sql
```

### Download files form pantheon (optional)

If you want to see files that contributors have uploaded to the site like images and pdfs:

Add to .ssh/config

```
Host pantheon-files
  HostName hostname-from-pantheon
  Port 2222
  User username-from-pantheon
  IdentityFile ~/.ssh/your-identity-file.id_rsa
```

rsync -rlvz --size-only --ipv4 --progress -e 'ssh -p 2222' pantheon-files:~/files/ ./sites/default/files

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

$databases['default']['default'] = array(
  'driver' => 'mysql',
  'database' => 'your-local-db',
  'username' => 'your-username',
  'password' => 'your-password',
  'host' => 'localhost',
  'prefix' => '',
  'collation' => 'utf8mb4_general_ci',
);
```

## Development

### Install composer

### Install Drush

`composer require "drush/drush:dev-master"`

### Run tests locally

Set your drupal_root in behat.yml

Start selenium server: `java -jar selenium-server-standalone-2.50.1.jar`

`./vendor/bin/behat`


### Import/export of configuration changes

As you make changes to the site, you'll want to export your configuration changes:

`drush cex -y`

And to set the local database to the configuration stored in git:

`drush cim -y`
