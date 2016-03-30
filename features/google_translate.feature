Feature: Translating the site
    As a anonymous user
    I need to translate the site
    So I can understand it

@javascript
Scenario: Translating the homepage
    Given I am on the homepage
    When I wait for AJAX to finish
    Then I should see the link 'Translate'
