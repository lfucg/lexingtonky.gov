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
