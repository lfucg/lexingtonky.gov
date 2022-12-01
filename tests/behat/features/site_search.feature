Feature: Searching the site
    As a anonymous user
    I need to search the site
    So I can find the pages I need

Scenario: Searching from the homepage
    Given I am on the homepage
    When I fill in "search_api_fulltext" with "About the site"
    And press "Search"
    Then I should see "about-the-site"

Scenario: Searching from an interior page
    Given I am on "/browse/government"
    When I fill in "search_api_fulltext" with "About the site"
    And press "Search"
    Then I should see "about-the-site"
