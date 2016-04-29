@api
Feature: Create and manage organization page

@in-progress @javascriptx
Scenario: Create unique organization page
  Given I am logged in as a user with the "administrator" role
  And I am on "admin/structure/taxonomy/manage/organizations/add"
  And I fill in "Name" with "lksdjflksjdflkjsdlksdajdf"
  And I press "Save"

  And I am on "/node/add/organization_page"
  And I fill in "Organization" with "foo"
  And I select "lksdjflksjdflkjsdlksdajdf" from "Organization taxonomy term"
  And I press "Save and publish"

  When I am on "/node/add/organization_page"
  Then the response should not contain "lksdjflksjdflkjsdlksdajdf"

  # And I fill in "Organization" with "foo"
  # And I select "aldkfjlajsdlfjdl" from "Organization taxonomy term"
  # And I press "Save and publish"
  #
  # Then I should see "Organization taxonomy term field is required"
  # And I break
  # Then the response should contain the randomized text "aa My department"
  # Then I should see randomized text "My department"

  # Given I fill in "Organization" with "foo"
  # And I fill in "Organization taxonomy term" with "My department term"
  # And I press "Save and publish"
  #
  # And I break
  # Then the response should not contain "My department"
