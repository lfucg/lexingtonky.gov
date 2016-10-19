# Testing the project

The following will all be simpler once the Kalabax Behat plugin is released. Until then:

[Behat](http://docs.behat.org/en/v3.0/) is installed in vendor. We run the tests using the [Webdriver API](https://www.w3.org/TR/webdriver/)
to automate a browser.

Locally, you'll run install and start the selenium server as seen in the [continuous integration config](circle.yml).

Create a project clone to mimic the working directory. This is where we
will `composer install` dev dependencies.

create copy of working dir

```
mkdir dev-dependencies
rsync -vah ../site-working-dir/ dev-dependencies/
cd dev-dependencies
composer require drupal/drupal-extension:"^3.1" --no-interaction
composer require guzzlehttp/guzzle:"^6.0@dev" --no-interaction
```

* Set your drupal_root in behat.yml (e.g. /my/path/to/drupal/install)
* Start selenium server: `java -jar selenium-server-standalone-2.50.1.jar` (other versions probably work)
* enable devel_mail_log: `drush en -y devel`

To run tests, make sure you have latest features in the dev-dependecies dir

```
cd dev-dependencies
rsync -vah ../site-working-dir/features/ features/
./vendor/bin/behat

# as a one-liner
rsync -vah ../site-working-dir/features/ features/ && ./vendor/bin/behat
```

## Backstory

This solution is imperfect. If we `composer install` dev dependencies to `site-working-dir/vendor`,
git will show a ton of changes since vendor can't be gitignored. So our local working
directory is a mess. Pantheon doesn't have a great way to deploy vendored files at build time.
To keep this project clean, we keep `vendor/` perfectly in sync with [Pantheon's Drupal 8 upstream](https://github.com/pantheon-systems/drops-8/).  We then install dev dependencies to a cloned version
of the working directory.

## Testing javascript

We use mocha to test js. See [circle.yml] for the npm commands to install and run js tests.

## Common issues with the chosen widget

Adding the @javascript tag to a test is helpful to watch selenium walk through tests
and break using Firefox. This is great when you're debugging something and want to look
at a page mid-way through the test. But having javascript enabled
means that you have to jump through hoops
to select items from pulldown menus that us the `chosen` widget. One thing you can do is
`drush pm-uninstall chosen` while you are debugging tests so that selecting from pulldowns
with or without javascript is the same. Once you finish debugging, you can
re-enable chosen.

## Behat commands

List behat steps available:

    $ ./vendor/bin/behat -dl
    default | Given I am on :urlPath with a random querystring
    default | Then I should see my page with a random querystring
    default | Then I should see :urlPath with a random querystring
    default | When I fill in :label with randomized text :text
    ...

For extended info

    $ ./vendor/bin/behat -dl
    $ vendor/bin/behat --definitions='content'

Add breakpoint

    Then I break

Behat help

    $ ./vendor/bin/behat -h

## Continuous integration

We [use CircleCI](circle.yml) to test against the Pantheon environment where our
live site is hosted. The tests run against cloned versions of the live site.

