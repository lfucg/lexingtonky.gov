@api
Feature: Integration of GIS endpoints into final order

Scenario: Verify all elements of Final Orders Page are present
    Given I am on "/final-orders"
    Then the response should contain "Final Order Date"
    And the response should contain "Property owner"
    And the response should contain "Address of violation"
    And the response should contain "Final order and related documents"
    And the response should contain "Sign-up for notifications"

@javascript
Scenario: Populating address from PVANUM
    Given I am logged in as a user with the "Final order admin" role
    And I am on "/node/add/final_order"
    And I fill in "PVA Num" with "04010003"
    And I press the "tab" key in the "PVA Num" field
    And I wait for AJAX to finish
    Then the "Address" field should contain "3534 TATES CREEK RD 40517"
    Then the "Last known mailing address of owner" field should contain "3534 TATES CREEK RD LEXINGTON, KY 40517"

Scenario: Create final order links appear
    Given I am logged in as a user with the "Final order admin" role
    And I am on "/final-orders"
    Then the response should contain "Add final order"

Scenario: Create final order links do not appear for public
    Given I am not logged in
    And I am on "/final-orders"
    Then the response should not contain "Add final order"

@javascript
Scenario: Search for a Final Order
    Given I am on "/final-orders"
    And I fill in "Search for" with "Norman"
    And I click on "input[value=Apply]" element
    And I wait for AJAX to finish
    Then the response should contain "234 CARLISLE AVE 40505"
