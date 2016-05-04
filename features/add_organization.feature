@api
Feature: Create and manage organization page

@javascript @in-progress
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
  And I am on "/departments"
  When I fill in "What's the latest from" with randomized text "new department"
  Then I should not see randomized text "my department"

@javascript
Scenario: Filtering departments the department directory
  Given I am on "/departments"
  Then I should see "Planning"

  When I fill in "What's the latest from" with "Accounting"
  Then I should not see "Planning"
  And I should see "Accounting"
