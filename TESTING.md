# Testing the project

[Behat](http://docs.behat.org/en/v3.0/) is installed in vendor. We run the tests using the [Webdriver API](https://www.w3.org/TR/webdriver/)
to automate a browser.

Locally, you'll run install and start the selenium server as seen in the [continuous integration config](circle.yml).

Then you can run the tests:

    ./vendor/bin/behat

or limit to certain tests:

    ./vendor/bin/behat --tags=in-progress

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

