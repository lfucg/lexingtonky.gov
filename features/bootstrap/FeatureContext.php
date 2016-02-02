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
     * @BeforeSuite
     */
    public static function beforeSuite() {
      exec('drush cache-clear render');
      exec('drush cache-clear css-js');
    }

    /**
     * Initializes context.
     *
     * Every scenario gets its own context instance.
     * You can also pass arbitrary arguments to the
     * context constructor through behat.yml.
     */
    public function __construct() {
      $this->randomString = (new Random())->word(10);
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

    /**
     * @Given I am on :urlPath with a random querystring
     */
    public function iAmOnWithARandomQuerystring($urlPath) {
      $this->minkContext->visit($urlPath . "?" . $this->randomString);
    }

    /**
    * @Then I should see my page with a random querystring
    */
    public function iShouldSeeMyPageWithARandomQuerystring() {
      $this->minkContext->assertPageContainsText("/browse/government?" . $this->randomString);
    }

    /**
     * @Then I should see :urlPath with a random querystring
     */
    public function iShouldSeeWithARandomQuerystring($urlPath) {
      $this->minkContext->assertPageContainsText($urlPath . "?" . $this->randomString);
    }
}
