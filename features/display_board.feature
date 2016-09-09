@api
Feature: Display board/commission page

@javascript
Scenario: Filtering boards in the department directory
  Given I am on "/all-boards-commissions"
  Then I should see "Planning Commission"

  When I fill in "Type the name of a board" with "Corridors"
  Then I should not see "Planning Commission"
  And I should see "Corridors Commission" in the content region
