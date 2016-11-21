Testing Drupal Examples for Developers
======================================

The Drupal Examples for Developers project uses DrupalCI testing on drupal.org.

That means: It runs the testbot on every patch that is marked as 'Needs Review.'

Your patch might not get reviewed, and certainly won't get committed unless it
passes the testbot.

The testbot runs a script that's in your Drupal installation called
`core/scripts/run-tests.sh`. You can run `run-tests.sh` manually and approximate
the testbot's behavior.

You can find information on how to run `run-tests.sh` locally here:
https://www.drupal.org/node/645286

You should at least run `run-tests.sh` locally against all the changes in your
patch before uploading it.

Keep in mind that unless you know you're changing behavior that is being tested
for, the tests are not at fault. :-)

Note also that, currently, using the `phpunit` tool under Drupal 8 will not find
PHPUnit-based tests in submodules, such as phpunit_example. There is no
suggested workaround for this, since there is no best practice to demonstrate as
an example. There is, however, this issue in core:
https://www.drupal.org/node/2499239


What Tests Should An Example Project Have?
------------------------------------------

Examples has a checklist for each module:
https://www.drupal.org/node/2209627

The reason we care about these tests is that we want the documentation
of these APIs to be correct. If Core changes APIs, we want our tests to
fail so that we know our documentation is incorrect.

Our list of required tests includes:
* Functional tests which verifies a 200 result for each route/path defined by
    the module.
* Functional tests of permission-based restrictions.
* Functional tests which submit forms and verify that they behave as
    expected.
* Unit tests of unit-testable code.
* Other. More. Better.
