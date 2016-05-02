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
      // exec('drush cache-clear render');
      // exec('drush cache-clear css-js');
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

    /*
     * randomString exists for the life of a scenario
     * The randomization means a test can be run against the same db
     * without bumping into text from previous run (since it's impractical to reset db)
    */
    public function randomizedText($text)
    {
        return $text . $this->randomString;
    }


    /** @BeforeScenario */
    public function gatherContexts(BeforeScenarioScope $scope) {
      $environment = $scope->getEnvironment();
      $this->minkContext = $environment->getContext('Drupal\DrupalExtension\Context\MinkContext');
      $this->drupalContext = $environment->getContext('Drupal\DrupalExtension\Context\DrupalContext');
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

    /**
     * @When I fill in :label with randomized text :text
     */
    public function iFillInWithRandomizedText($label, $text)
    {
      $this->minkContext->fillField($label, $this->randomizedText($text));
    }

    /**
     * @Then I should see randomized text :text
     */
    public function iShouldSeeRandomizedText($text)
    {
      $this->minkContext->assertPageContainsText($this->randomizedText($text));
    }

    /**
     * @Then I should not see randomized text :text
     */
    public function iShouldNotSeeRandomizedText($text)
    {
      $this->minkContext->assertPageNotContainsText($this->randomizedText($text));
    }

    /**
     * @Then I fill in :label with my name
     */
    public function iFillInWithMyName($label)
    {
      $this->minkContext->fillField($label, $this->drupalContext->user->name);
    }


    public function findBySelector($selector)
    {
        $element = $this->minkContext->getSession()->getPage()->find("css", $selector);
        if (!$element) {
            throw new Exception($selector . " selector could not be found");
        }
        return $element;
    }

    /**
     * @When I fill in :selector element with :value
     */
    public function iFillInElementWith($selector, $value)
    {
        $this->findBySelector($selector)->setValue($value);
    }

    /**
     * @When I click on :selector element
     */
    public function iClickOnElement($selector)
    {
        $this->findBySelector($selector)->click();
    }

    /**
     * @Given I select randomized text :text from :select
     */
    public function iSelectRandomizedTextFrom($text, $select)
    {
        $this->minkContext->selectOption($select, $this->randomizedText($text));
    }

    /**
     * @Then the response should not contain randomized text :text
     */
    public function theResponseShouldNotContainRandomizedText($text)
    {
        $this->minkContext->assertResponseNotContains($this->randomizedText($text));
    }
}
