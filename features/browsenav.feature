Feature: Using the browse navigation
    As a anonymous user
    I need to navigate the taxonomy
    So I can find the service I need

@javascript
Scenario: Using ajax navigation
    Given I am on "/browse/government"
    Then I should see text matching "A.*to.*Z"

    When I click "Public safety"
    And I wait for AJAX to finish
    And I click "Police"
    And I wait for AJAX to finish
    Then I should see the link 'Crime Map'
    # breadcrumb
    And I should see "Home Public safety"

Scenario: Using plain html navigation
    Given I am on "/browse/government"
    Then I should see text matching "A.*to.*Z"

    When I click "Public safety"
    And I click "Police"
    Then I should see the link 'Crime Map'
    And I should see text matching "A.*to.*Z"
    # breadcrumb
    And I should see "Home Public safety"
