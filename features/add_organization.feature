@api
Feature: Create and manage organization page

@javascript
Scenario: Filtering departments the department directory
  Given I am on "/departments"
  Then I should see "Planning"

  When I fill in "What's the latest from" with "Accounting"
  Then I should not see "Planning"
  And I should see "Accounting"

Scenario: Displaying news articles on an organization page
  Given I am logged in as a user with the "editor" role
  And I am on "node/add/news_article"
  And I fill in "Title" with randomized text "New article"
  And I fill in "Body" with "foo"
  And I select "-Police" from "Related departments"
  And I press "Save and publish"

  When I am on "/departments/police"
  And I should see randomized text "New article"

Scenario: Displaying events on an organization page
  Given I am logged in as a user with the "editor" role
  And I am on "node/add/event"
  And I fill in "Title" with randomized text "New event"
  And I select "Addison Park" from "Location"

  # dept w/o events
  And I select "-Accounting" from "Related departments"
  And I fill in "Cost" with "free"
  And I fill in "edit-field-date-end-0-value-date" with "2050-01-01"
  And I press "Save and Publish"

  When I am on "/departments/accounting"
  Then I should see randomized text "New event"

Scenario: Add department page to topic navigation
  Given I am logged in as a user with the "editor" role
  # computer services: an organization that doesn't have a topic
  And I am on "/node/476/edit"
  And I select "-Senior Programs" from "Navigation topic (optional)"
  And I press "Save and keep published"

  When I am on "/browse/community-services/senior-programs"
  Then I should see the link "Computer Services"
