@api
Feature: Filter and display neighborhood associations,

@javascript @in-progress
Scenario: Filtering associations in the directory
  Given I am on "/find-your-neighborhood-association"
  And I wait for AJAX to finish
  And I wait for 1000 miliseconds
  Then I should see "ANDOVER"
