@api
Feature: Drupal Extension
    In order to prove that the Behat Drupal extension and Blackbox driver are working
    As a developer
    I need to test some scenarios using definitions in the Drupal Extension

@javascript
Scenario: Create many nodes
  Given "page" content:
  | title    |
  | Page one |
  | Page two |
  And I am logged in as a user with the "administrator" role
  When I go to "admin/content"
  Then I should see "Page one"
  And I should see "Page two"
