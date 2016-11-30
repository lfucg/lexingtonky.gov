@api
Feature: Integration of GIS endpoints into final order

@javascript
Scenario: Populating address from PVANUM
    Given I am logged in as a user with the "Final order admin" role
    And I am on "/node/add/final_order"
    And I fill in "PVA Num" with "04010003"
    When I fill in "Title" with "Trigger blur event in the PVA field"
    And I wait for AJAX to finish
    Then the "Address" field should contain "3534 TATES CREEK RD"
    Then the "Last known mailing address of owner" field should contain "3534 TATES CREEK RD"

Scenario: Create final order links appear
    Given I am logged in as a user with the "Final order admin" role
    # Enable once a final order exists in prod
    # When I am on "/node/1943"
    # Then the response should contain "Add final order"

    # Enable once a final order menu exists in prod
    # Given I am on "/final-orders"
    # Then the response should contain "Add final order"
