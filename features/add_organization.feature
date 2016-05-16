@api
Feature: Create and manage organization page

@javascript
Scenario: Create unique organization page
  Given I am logged in as a user with the "webmaster" role
  And I am on "admin/structure/taxonomy/manage/organizations/add"
  And I fill in "Name" with randomized text "new department"
  And I press "Save"

  And I am on "/node/add/organization_page"
  And I fill in "Organization" with "foo"
  And I fill in "Organization taxonomy term" with randomized text "new department"
  And I press the "Save" button

  When I am on "/node/add/organization_page"
  And I fill in "Organization" with "foo"
  And I fill in "Organization taxonomy term" with randomized text "new department"
  And I press "Save"
  Then I should see the error message containing 'There are no entities matching "new department'

  # Make sure webmaster can publish org pages
  Given I am not logged in
  When I am on "/departments"
  Then I should see randomized text "new department"

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
  # a random event
  And I am on "/node/273/edit"
  And I fill in "Title" with randomized text "Updated event"
  And I select "-Police" from "Related departments"
  And I fill in "edit-field-date-end-0-value-date" with "2050-01-01"
  And I press "Save and keep published"

  When I am on "/departments/police"
  Then I should see randomized text "Updated event"

# this will have to be enabled after the org page migration 20160516
# so we can pick the node id of org page
# @in-progress
# Scenario: Add department page to topic navigation
#   Given I am logged in as a user with the "editor" role
#   # an organization that doesn't have a topic
#   And I am on "/node/440/edit"
#   And I fill in "Title" with randomized text "Department page title"
#   And I select "-Senior Programs" from "Navigation topic (optional)"
#   And I press "Save and keep published"
#
#   When I am on "/browse/community-services/senior-programs"
#   Then I should see randomized text "Department page title"
