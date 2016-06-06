@api
Feature: Add and Manage service guide content for the 'Needs Review' rss-feed view

  Scenario: Save content as a draft, check to see it IS NOT on page /content-needing-review,
  Edit content to 'needs review' and check that it IS on page /content-needing-review
    Given I am logged in as a user with the "authenticated user" role
    And I am on "/node/add/page"
    And I fill in "Title" with "apax"
    And I select "-Councilmembers" from "Navigation topic"
    And I press "Save and Create New Draft"
    And I am on "/content-needing-review.xml"
    Then the response should not contain "apax"

    And I am on "/apax"
    And I click "Edit draft"
    And I press "Save and Request Review"
    When I am on "/content-needing-review.xml"
    Then the response should contain "apax"

  Scenario: Save content as 'Needs Review', check to see it IS on page /content-needing-review,
  Edit content to 'Save and publish' and check that it IS NOT on page /content-needing-review
    Given I am logged in as a user with the "editor" role
    And I am on "/node/add/page"
    And I fill in "Title" with "apaxsoftware"
    And I select "-Councilmembers" from "Navigation topic"
    And I press "Save and Request Review"
    Then the url should match "apaxsoftware"
    And I am on "/content-needing-review.xml"
    Then the response should contain "apaxsoftware"
    And I am on "/apaxsoftware"
    And I click "Edit draft"
    And I press "Save and Publish"
    When I am on "/content-needing-review.xml"
    Then the response should not contain "apaxsoftware"
