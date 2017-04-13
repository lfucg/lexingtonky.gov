Feature: Translating the site
    As a anonymous user
    I need to translate the site
    So I can understand it

@javascript
Scenario: Translating the homepage
    Given I am on the homepage
    When I wait for AJAX to finish
    And I wait for 4000 miliseconds
    Then I should see the link 'Translate'
