<?php

use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;

use Drupal\Component\Utility\Random;

/**
 * Defines application features from the specific context.
 */
class FeatureContext implements Context, SnippetAcceptingContext {

    /**
     * @var randomString
     */
    private $randomString;

    /**
     * Initializes context.
     *
     * Every scenario gets its own context instance.
     * You can also pass arbitrary arguments to the
     * context constructor through behat.yml.
     */
    public function __construct() {
      $this->randomString = (new Random())->string();
    }

    /**
     * @BeforeSuite
     */
    public static function beforeSuite() {
      exec('drush cache-clear render');
      exec('drush cache-clear css-js');
    }

    /** @BeforeScenario */
    public function gatherContexts(BeforeScenarioScope $scope) {
      $environment = $scope->getEnvironment();
      $this->minkContext = $environment->getContext('Drupal\DrupalExtension\Context\MinkContext');
    }

    /**
     * @Given /^I fill in "([^"]*)" with random text$/
     */
    public function iFillInWithRandomText($label) {
      $this->minkContext->fillField($label, $this->randomString);
    }

    /**
     * @Then I should see the random text
     */
    public function iShouldSeeTheRandomText() {
      $this->minkContext->assertPageContainsText($this->randomString);
    }
}
