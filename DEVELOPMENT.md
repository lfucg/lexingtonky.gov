# Development

The following instructions to set up a local development environment for the city's Drupal site.

## Local installation

**First, try kalabox** It makes local development setup very easy and is always improving.

## Running tests locally

[See TESTING.md](TESTING.md)

## Deployment

After merging changes to master and pushing to github, Circle CI will deploy those changes to the Pantheon `dev` environment (if the tests pass).

Next: Deploy to `test`

`./deploy-to-pantheon.sh test`

After manual smoke testing: Deploy to `live`

`./deploy-to-pantheon.sh live`

## If Kalabox doesn't work, try the manual approach

An [example walkthrough of install process](http://erikschwartz.net/2015-11-16-install-pantheon-drupal-8-mamp) on a Mac.

[Install terminus](https://github.com/pantheon-systems/terminus)

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
terminus site backups get --element=database --env=live --to=. --latest
gunzip --stdout <your-site_2016-03-11T13-00-00_UTC_database.sql.gz> | mysql -u <username> -p<password> <local-db-name>
drush cr
```

### Download files from pantheon (optional)

If you want to see files that contributors have uploaded to the site like images and pdfs:

Add to .ssh/config

```
Host pantheon-files
  HostName hostname-from-pantheon
  Port 2222
  User username-from-pantheon
  IdentityFile ~/.ssh/your-identity-file.id_rsa
```

`rsync -rlvz --size-only --ipv4 --progress -e 'ssh -p 2222' pantheon-files:~/files/ ./sites/default/files`
[Install composer](https://getcomposer.org/doc/00-intro.md)

Install Drush

`composer require "drush/drush:dev-master"`

### Import/export of configuration changes

As you make changes to the site, you'll want to export your configuration changes:

`drush cex -y`

And to set the local database to the configuration stored in git:

`drush cim -y`

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

## Debugging issues reported by contributors

Create a multi-dev environment with content cloned from live.

Visit /admin/modules and enable `masquerade`

Go to the contributor's page and 'Masquerade as jsmith` to reproduce the issue.
