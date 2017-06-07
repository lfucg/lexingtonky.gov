@api
Feature: Create and edit meetings

Scenario: Meetings appear on calendar
  Given I am logged in as a user with the "editor" role
  And I am on "/node/add/meeting"
  And I fill in "Title" with "very-unique-meeting"
  And I select "Addison Park" from "Location"
  And I select "-Accounting" from "Related departments"

  When I fill in "edit-field-date-0-value-date" with "2045-07-18"
  And I fill in "edit-field-date-0-value-time" with "09:30"
  # optional end date
  # And I fill in "edit-field-date-end-0-value-date" with "<end date>"
  # And I fill in "edit-field-date-end-0-value-time" with "<end time>"
  And I press the 'Save and Publish' button

  Then I should see "Meetings and notices" in the breadcrumb region

  When I am on "/departments/accounting"
  Then I should see "very-unique-meeting"

# Scenario: You can add a meeting to Outlook calendar
#   Given I am on "/meeting-notices/904/council-work-session"
#   When I click "Add to Outlook calendar"
#   Then the response status code should be 200
