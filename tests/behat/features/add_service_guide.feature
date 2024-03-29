@api
Feature: Create and edit service guides

@javascript
Scenario: Use chosen widget to select navigation topic
  Given I am logged in as a user with the "administrator" role
  And I am on "/node/add/page"
  And I fill in "Title" with randomized text "New page title"
  And I fill in "Navigation topic" with "Councilmembers"
  And I press "List additional actions"
  And I open save options
  And I press "Save and Publish"
  Then the url should match "new-page-title"
  ## Make sure the chosen widget worked
  # EDS: config sync issue hiding this from admins on CI
  # And I should see the link "Councilmembers"

Scenario: Follow publishing workflow
  Given I am logged in as a user with the "authenticated user" role
  When I am on "/node/add/page"
  Then the response should not contain "Save and Publish"

  Given I fill in "Title" with "Test title"
  And I fill in "Navigation topic" with "Councilmembers"
  And I open save options
  And I press "Save and Request Review"
  When I click "Edit draft"
  Then I should see "Not published"
  And the response should not contain "Save and Publish"

  Given I am logged in as a user with the "editor" role
  And I visit "/test-title"
  And I click "Edit draft"
  And I open save options
  And I press "Save and Publish"
  When I am not logged in
  And I visit "/test-title"
  Then the response should contain "Test title"

Scenario: Editor directly publishes
  Given I am logged in as a user with the "editor" role
  When I am on "/node/add/page"
  Then the response should contain "Save and Publish"

Scenario: Editing a page doesn't remove it from browse navigation
  Given I am logged in as a user with the "editor" role
  # District 12 page
  When I am on "/node/123/edit"
  And I open save options
  And I press "Save and Create New Draft"
  And I am on "/browse/government/council"
  Then I should see the link "Council District 12"
  And the response should contain "<title>Councilmembers | City of Lexington</title>"

Scenario: Unpublished nodes do not show up in browse nav
  Given I am logged in as a user with the "authenticated user" role
  When I am on "/node/add/page"
  And I fill in "Title" with randomized text "Test title"
  And I fill in "Navigation topic" with "Councilmembers"
  And I open save options
  And I press "Save and Request Review"
  And I am on "/browse/government/council"
  Then I should not see randomized text "Test title"
