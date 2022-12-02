# lexingtonky.gov

[![CircleCI](https://circleci.com/gh/lfucg/lexingtonky.gov.svg?style=shield)](https://circleci.com/gh/lfucg/lexingtonky.gov)

## Lando with Docker
Be sure you have the latest release of Lando for local development. The .lando.yml at the root of the repo is the driving force behind setting things up.

https://docs.lando.dev/basics/installation.html

## Get up and running for the first time
From the root directory of the project:

```console
$ lando start
$ cp web/sites/example.settings.local.php web/sites/default/settings.local.php
$ lando pull -c none -d dev -f dev
```

## Pantheon authentication
If you haven't setup a [machine token](https://pantheon.io/docs/machine-tokens), you'll need to. Then, with lando running, you can authenticate via:

```bash
$ lando terminus auth:login --email=<email> --machine-token=<machine-tocken>
```

## Workflow

### Drupal

1. Run `lando start` from the root directory of the project.
3. Run `lando composer install` from the root directory to get latest installed composer managed dependencies
4. `lando drush cim` then `lando drush cr`
5. Work work work work work.
6. `lando drush cex`
7. Track files in git

### Theme
From the `web/themes/custom/lex` directory, you can watch theme files and rebuild by running:

```bash
$ lando gulp
```

If dependencies change, you can leverage the `lando npm` tooling.
