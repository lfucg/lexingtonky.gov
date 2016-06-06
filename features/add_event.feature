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
  Then the response should not contain "Save and Publish"

Scenario Outline: Event dates and times are formatted correctly
  Given I am logged in as a user with the "editor" role
  And I am on "/node/add/event"
  And I fill in "Title" with "very-unique-event"
  And I select "Addison Park" from "Location"
  And I select "-Accounting" from "Related departments"
  And I fill in "Cost" with "free"

  When I fill in "edit-field-date-0-value-date" with "<begin date>"
  And I fill in "edit-field-date-0-value-time" with "<begin time>"
  And I fill in "edit-field-date-end-0-value-date" with "<end date>"
  And I fill in "edit-field-date-end-0-value-time" with "<end time>"
  And I press the 'Save and Publish' button

  Then I should see "<date format>"

  When I am on "/departments/accounting"
  Then I should see "<time to show>"

  Examples:
    | begin date | begin time | end date   | end time | date format                                   | time to show |
    | 2055-04-26 | 09:33      | 2055-04-26 | 10:33    | Apr 26, 2055 9:33 – 10:33 a.m.                | 10:33 |
    | 2055-04-26 | 09:30      | 2055-04-26 | 12:30    | Apr 26, 2055 9:30 a.m. – 12:30 p.m.           |  |
    | 2055-04-26 | 09:30      | 2055-04-27 | 10:00    | Apr 26, 2055 9:30 a.m. – Apr 27, 2055 10 a.m. |  |

Scenario Outline: Event start/end times can be hidden
  Given I am logged in as a user with the "editor" role
  And I am on "/node/add/event"
  And I fill in "Title" with randomized text "New event"
  And I select "Addison Park" from "Location"
  And I fill in "Cost" with "free"

  When I fill in "edit-field-date-0-value-date" with "<begin date>"
  And I fill in "edit-field-date-0-value-time" with "<begin time>"
  And I fill in "edit-field-date-end-0-value-date" with "<end date>"
  And I fill in "edit-field-date-end-0-value-time" with "<end time>"
  And I check the box 'Hide start and end times'
  And I check the box 'Promoted to front page'
  And I press the 'Save and Publish' button

  Then I should see "<date format>"
  And I should not see "<time to hide>"

  When I am on the homepage
  Then I should not see "<time to hide>"

  Examples:
    | begin date | begin time | end date   | end time | date format           | time to hide |
    | 2016-04-26 | 09:33      | 2055-04-26 | 10:30    | Apr 26, 2016          | 9:33 |
    | 2016-04-26 | 09:33      | 2055-04-27 | 10:00    | Apr 26, 2016 – Apr 27 | 9:33 |
