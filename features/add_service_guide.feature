@api
Feature: Create and edit basic pages

@javascript
Scenario: Use chosen widget to select navigation topic
  Given I am logged in as a user with the "administrator" role
  And I am on "/node/add/page"
  And I fill in "Title" with randomized text "The title of my page"
  And I click on '.chosen-single' element
  And I fill in ".chosen-search input" element with "council"
  And I press "List additional actions"
  And I press "Save and Publish"
  Then the url should match "title-of-my-page"
  ## Make sure the chosen widget worked
  And I should see the link "Urban County Council"

Scenario: Follow publishing workflow
  Given I am logged in as a user with the "authenticated user" role
  When I am on "/node/add/page"
  Then the response should not contain "Save and Publish"

  Given I fill in "Title" with "Test title"
  And I select "-Urban County Council" from "Navigation topic"
  And I press "Save and Request Review"
  When I click "Edit draft"
  Then I should see "Not published"
  And the response should not contain "Save and Publish"

  Given I am logged in as a user with the "editor" role
  And I visit "/test-title"
  And I click "Edit draft"
  And I press "Save and Publish"
  When I am not logged in
  And I visit "/test-title"
  Then the response status code should be 200

Scenario: Editor directly publishes
  Given I am logged in as a user with the "editor" role
  When I am on "/node/add/page"
  Then the response should contain "Save and Publish"
