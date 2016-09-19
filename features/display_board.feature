@api
Feature: Display board/commission page

@javascript
# Scenario: Filtering boards in the department directory
#   Given I am on "/browse/government"
#   # make sure js boards directory initializes when loaded via ajax
#   And I click "Boards and commissions"
#   And I wait for AJAX to finish
#   Then I should see "Planning Commission"
#
#   When I fill in "Type the name of a board" with "Corridors"
#   Then I should not see "Planning Commission"
#   And I should see "Corridors Commission" in the content region
#
# Scenario: Updating a board
#   Given I am logged in as a user with the "editor" role
#   # Arboretum Advisory Board: node will change on deploy!
#   When I am on "/node/1669/edit"
#   Then the response should contain "Save and Publish"
#
#   Given I am logged in as a user with the "authenticated user" role
#   # Arboretum Advisory Board: node id will change on deploy!
#   When I am on "/node/1669/edit"
#   Then the response should not contain "Save and Publish"
#   And the response should contain "Arboretum Advisory Board"
#
