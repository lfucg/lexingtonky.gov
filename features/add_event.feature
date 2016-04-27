@api
Feature: Create and edit events

Scenario: Contributors can create events
  Given I am logged in as a user with the "authenticated user" role
  When I am on "/node/add"
  And I should see the link "Event"

Scenario: Contributors can edit events
  Given I am logged in as a user with the "authenticated user" role
  # a random event
  When I am on "/node/273/edit"
  Then I should get a 200 HTTP response

Scenario: Contributors can not publish events
  Given I am logged in as a user with the "authenticated user" role
  # a random event
  When I am on "/node/273/edit"
  Then the response should not contain "Save and keep published"

Scenario Outline: Dates are formatted correctly
  Given I am logged in as a user with the "editor" role
  And I am on "/node/add/event"
  And I fill in "Title" with randomized text "New event"
  And I select "Addison Park" from "Location"
  And I fill in "Cost" with "free"

  When I fill in "edit-field-date-0-value-date" with "<begin date>"
  And I fill in "edit-field-date-0-value-time" with "<begin time>"
  And I fill in "edit-field-date-end-0-value-date" with "<end date>"
  And I fill in "edit-field-date-end-0-value-time" with "<end time>"
  And I press the 'Save and publish' button

  Then I should see "<date format>"

  Examples:
    | begin date | begin time | end date   | end time | date format                                   |
    | 2016-04-26 | 09:30      | 2016-04-26 | 10:30    | Apr 26, 2016 9:30 – 10:30 a.m.                |
    | 2016-04-26 | 09:30      | 2016-04-26 | 12:30    | Apr 26, 2016 9:30 a.m. – 12:30 p.m.           |
    | 2016-04-26 | 09:30      | 2016-04-27 | 10:00    | Apr 26, 2016 9:30 a.m. – Apr 27, 2016 10 a.m. |
