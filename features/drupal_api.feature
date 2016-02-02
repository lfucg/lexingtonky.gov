@api
Feature: Per-page comment
    To submit a comment on the current page
    As an anonymous visitor
    I need to submit comments for various pages

@javascript
Scenario: Submit homepage feedback
   Given I am on the homepage
   And I click 'Is there anything wrong with this page?'
   And I fill in "What went wrong" with random text
   And I press "Send message"
   And I am logged in as a user with the "administrator" role
   When I go to "admin/structure/contact/messages"
   Then I should see the random text
