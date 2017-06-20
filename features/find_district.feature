@api
Feature: Anyone can enter an address to look up its council district

@javascript
Scenario: Enter an address to find its district
    Given I am on "/council-district-5"
    When I fill in "Type an address to find your council district" with "200 E Main st"
    ## Triggers JS autocomplete
    And I press the "r" key in the "Type an address to find your council district" field
    And I wait for 2000 miliseconds
    #Then I should see "200 E MAIN ST"

    When I click on '.ui-menu-item' element
    And I wait for 2000 miliseconds
    Then I should see the link "Council District 3"
