@api
Feature: Per-page comment
    To submit a comment on the current page
    As an anonymous visitor
    I need to submit comments for various pages

@javascript
Scenario: Submit homepage feedback
    Given I am on the homepage
    # hidden by javascript accordion
    Then I should not see "What could we do better?"

    And I press "Suggestions or problems with this page?"
    When I fill in "What could we do better?" with random text
    And I press "Send message"
    Given I wait for AJAX to finish
    Then I should see "Thank you"

    # Doesn't work in phantomjs currently
    # And I am logged in as a user with the "administrator" role
    # When I go to "admin/structure/contact/messages"
    # Then I should see the random text

@javascript
Scenario: Submit interior page feedback
    Given I am on "/browse/government" with a random querystring
    And I press "Suggestions or problems with this page?"
    # hidden field
    Then I should not see "Feedback URL"

    # Doesn't work in phantomjs currently
    # And I press "Send message"
    # Given I wait for AJAX to finish
    # And I am logged in as a user with the "administrator" role
    # When I go to "admin/structure/contact/messages"
    # Then I should see "/browse/government" with a random querystring
