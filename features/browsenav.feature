Feature: Using the browse navigation
    As a anonymous user
    I need to navigate the taxonomy
    So I can find the service I need

@javascript
Scenario: Using ajax navigation
    Given I am on "/browse/government"
# This is now displayed using CSS, so it isn't present in the DOM to be tested for.
#    Then I should see text matching "A.*to.*Z"

    When I click "Public safety"
    And I wait for AJAX to finish
    And I wait for 4000 miliseconds
    And I click "Police"
    And I wait for AJAX to finish
    And I wait for 4000 miliseconds
    Then I should see the link 'Crime Map'
    # breadcrumb
    And I should see "Home Public safety"

Scenario: Using plain html navigation
    Given I am on "/browse/government"
    Then I should see text matching "A.*to.*Z"

    When I click "Public safety"
    And I click "Police"
    Then I should see the link 'Crime Map'
# As above, this is now CSS controlled.
#    And I should see text matching "A.*to.*Z"
    # breadcrumb
    And I should see "Home Public safety"
