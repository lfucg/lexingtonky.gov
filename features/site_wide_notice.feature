@api
Feature: Per-page comment
    To submit a comment on the current page
    As an anonymous visitor
    I need to submit comments for various pages

Scenario: Create a site-wide alert
    Given I am logged in as a user with the "administrator" role
    And I am on "/node/add/site_wide_notice"
    And I fill in "Heading" with randomized text "A new notice"
    And I fill in "Body" with "fooo"
    And I press "Save and publish"

    When I am on the homepage
    Then I should see randomized text "A new notice"

# Enable once we create the site-wide alert
# Scenario: Unpublish a site-wide alert
#     Given I am logged in as a user with the "editor" role
#     # site-wide notice
#     Given I am on "/node/551/edit"
#     And I press "Save and unpublish"
#
#     When I am on the homepage
#     Then I should not see "City trash pickup for Monday, May 30 "
