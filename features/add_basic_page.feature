@api
Feature: Create and edit basic pages

@javascript
Scenario: Use chosen widget to select navigation topic
  Given I am logged in as a user with the "editor" role
  And I am on "/node/add/page"
  And I fill in "Title" with randomized text "The title of my page"
  And I click on '.chosen-single' element
  And I fill in ".chosen-search input" element with "council"
  And I press "List additional actions"
  And I press "Save and publish"
  When I am on "/browse/government/council"
  # make sure placed correctly in topic navigation
  Then I should see randomized text "The title of my page"
  And I click "The title of my page"
  Then the url should match "title-of-my-page"
