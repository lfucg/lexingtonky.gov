@api
Feature: Add and Manage service guide content for the 'Needs Review' rss-feed view

  Scenario: Save content as a draft, check to see it IS NOT on page /content-needing-review,
  Edit content to 'needs review' and check that it IS on page /content-needing-review
    Given I am logged in as a user with the "administrator" role
    And I am on "/node/add/page"
    And I fill in "Title" with "apax"
    And I select "-Urban County Council" from "Navigation topic"
    And I press "Save and Create New Draft"
    Then the url should match "apax"
    And I should see the link "Urban County Council"
    And I am on "/content-needing-review"
    Then I should not see the link "apax"
    And I am on "/apax"
    And I click "Edit draft"
    And I press "Save and Request Review"
    When I am on "/content-needing-review"
    Then I should see the link "apax"

  Scenario: Save content as 'Needs Review', check to see it IS on page /content-needing-review,
  Edit content to 'Save and publish' and check that it IS NOT on page /content-needing-review
    Given I am logged in as a user with the "administrator" role
    And I am on "/node/add/page"
    And I fill in "Title" with "apaxsoftware"
    And I select "-Urban County Council" from "Navigation topic"
    And I press "Save and Request Review"
    Then the url should match "apaxsoftware"
    And I should see the link "Urban County Council"
    And I am on "/content-needing-review"
    Then I should see the link "apaxsoftware"
    And I am on "/apaxsoftware"
    And I click "Edit draft"
    And I press "Save and Publish"
    When I am on "/content-needing-review"
    Then I should not see the link "apaxsoftware"
