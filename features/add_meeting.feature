@api
Feature: Create and edit meetings

# Scenario: Contributors can create events
#   Given I am logged in as a user with the "authenticated user" role
#   When I am on "/node/add"
#   And I should see the link "Event"
#
# Scenario: Contributors can edit events
#   Given I am logged in as a user with the "authenticated user" role
#   # a random event
#   When I am on "/node/273/edit"
#   Then I should get a 200 HTTP response
#
# Scenario: Contributors can not publish events
#   Given I am logged in as a user with the "authenticated user" role
#   # a random event
#   When I am on "/node/273/edit"
#   Then the response should not contain "Save and Publish"

@in-progress
Scenario: Meetings appear on calendar
  Given I am logged in as a user with the "editor" role
  And I am on "/node/add/meeting"
  And I fill in "Title" with "very-unique-meeting"
  And I select "Addison Park" from "Location"
  And I select "-Accounting" from "Related departments"

  # rely on the default start time which is now + 1 hour
  When I fill in "edit-field-date-0-value-date" with "2045-07-18"
  And I fill in "edit-field-date-0-value-time" with "09:30"
  # And I fill in "edit-field-date-end-0-value-date" with "<end date>"
  # And I fill in "edit-field-date-end-0-value-time" with "<end time>"
  And I press the 'Save and Publish' button

  Then I should see "Jul 18, 2045 9:30 a.m."
  And I should see "Meetings and notices" in the breadcrumb region

  # Handle no end time correctly
  Then I should not see "9:30 a.m. –"

  # When I am on "/departments/accounting"
  # Then I should see "very-unique-meeting"
  # Then I should not see "9:30 a.m. –"
  # Then I should see "<time to show>"
  # rely on the default start time which is now + 1 hour
