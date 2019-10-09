@api
Feature: Allow editors to manage a site-wide alert

Scenario: Create a site-wide alert
    Given I am logged in as a user with the "administrator" role
    And I am on "/node/add/site_wide_notice"
    And I fill in "Heading" with "A new notice"
    And I fill Hypertext in "edit-body-0-value" with "body of the notice"
    And I open save options
    And I press "Save and publish"
    When I am on the homepage
    Then I should see "body of the notice"

    Given I am on "/admin/content"
    And I click "Edit"
    And I open save options
    And I press "Save and unpublish"
    When I am on the homepage
    Then I should not see "body of the notice"
