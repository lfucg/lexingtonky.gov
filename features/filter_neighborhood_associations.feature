@api
Feature: Filter and display neighborhood associations,

@javascript
Scenario: Filtering associations in the directory
  Given I am on "/find-your-neighborhood-association"
  When I wait for AJAX to finish
  And I wait for 1000 miliseconds
  Then I should see "ANDOVER"

  Given I fill in "Type the name of a neighborhood association" with "cardinal"
  Then I should not see "ANDOVER"
  And I should see "CARDINAL VALLEY" in the content region


