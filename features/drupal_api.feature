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
   Then I should see "Thank you for helping us to improve lexingtonky.gov!"

   And I am logged in as a user with the "administrator" role
   When I go to "admin/structure/contact/messages"
   Then I should see the random text
