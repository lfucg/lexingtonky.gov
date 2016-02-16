@api
Feature: Per-page comment
    To submit a comment on the current page
    As an anonymous visitor
    I need to submit comments for various pages

# bootstrapping the browser is hard, so we test incrementally
@javascript
Scenario: Submit homepage feedback
    Given I am on the homepage
    # hidden by javascript accordion
    Then I should not see "What could we do better?"

    And I click "Suggestions"
    Then I should see "Would you like a reply"

    When I fill in "How could this page be better?" with randomized text "foo"
    And I press "Send message"
    Given I wait for AJAX to finish
    Then I should see "Thank you"

    And I am logged in as a user with the "administrator" role
    When I go to "admin/structure/contact/messages"
    Then I should see randomized text "foo"

@javascript
Scenario: Submit interior page feedback
    Given I am on "/browse/government" with a random querystring
    And I click "Suggestions or problems with this page?"
    ## hidden field
    Then I should not see "Feedback URL"

    And I press "Send message"
    Given I wait for AJAX to finish
    And I am logged in as a user with the "administrator" role
    When I go to "admin/structure/contact/messages"
    Then I should see "/browse/government" with a random querystring

@javascript
Scenario: Submit feedback from feedback page
    Given I am on "/contact/page_feedback"
    When I fill in "How could this page be better?" with randomized text "bar"
    And I press "Send message"
    Then I should be on "/"
    And I should see "Your message has been sent"

Scenario: Submit feedback without inline ajax form
    Given I am on the homepage
    And I click "Suggestions or problems with this page?"
    Then I should be on "/contact/page_feedback"

@javascript
Scenario: Editors mark when they have responded to feedback
    Given I am on "/contact/page_feedback"
    Then I should not see "City response from"

    When I fill in "Your email" with randomized text "foo@bar.com"
    And I press "Send message"
    And I am logged in as a user with the "editor" role
    And I go to "admin/structure/contact/messages"
    And I select "Yes" from "Email provided?"
    And I select "No" from "We've responded?"
    And I press "Apply"
    Then I should see randomized text "foo@bar.com"

    And I click "Edit"
    And I fill in "City response from" with my name
    And I press "Save"
    And I select "Yes" from "Email provided?"
    And I select "No" from "We've responded?"
    And I press "Apply"
    Then I should not see randomized text "foo@bar.com"
