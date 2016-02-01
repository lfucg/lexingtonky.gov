# This is a test
Feature: Drupal Extension
    In order to prove that the Behat Drupal extension and Blackbox driver are working
    As a developer
    I need to test some scenarios using definitions in the Drupal Extension

Scenario: Test going to the homepage
    Given I am on the homepage
    Then the response status code should be 200

Scenario: Test logo is on homepage
    Given I am on the homepage
    Then I should see a "#logo" element

Scenario: Test navigating to another page
    Given I am on the homepage
    When I click "Government"
    Then I should be on "/browse/government"
