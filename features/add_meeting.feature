@api
Feature: Create and edit meetings

@in-progress
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

  Then I should see "Jul 18, 2045 9:30 a.m."
  And I should see "Meetings and notices" in the breadcrumb region

  # Handle no end time correctly
  Then I should not see "9:30 a.m. –"

  When I am on "/departments/accounting"
  Then I should see "very-unique-meeting"
