@api
Feature: Create and manage organization page

@javascript
Scenario: Filtering departments in the department directory
  Given I am on "/browse/government"
  And I wait for 4000 miliseconds
  And I click "Departments and programs"
  And I wait for AJAX to finish
  Then I should see "Computer Services"

  When I fill in "Type the name of a department" with "Accounting"
  Then I should not see "Computer Services"
  And I should see "Accounting" in the content region

Scenario: On department without navigation topic
  Given I am on "/departments/computer-services"
  Then I should see "Home Government Departments and programs" in the breadcrumb region

Scenario: Displaying news articles on an organization page
  Given I am logged in as a user with the "editor" role
  And I am on "node/add/news_article"
  And I fill in "Title" with randomized text "New article"
  And I fill Hypertext in "edit-body-0-value" with "foo"
  And I select the term "-Police" by id "edit-field-related-departments"
  And I open save options
  And I press "Save and Publish"

  When I am on "/departments/police"
  And I should see randomized text "New article"

Scenario: Displaying events on an organization page
  Given I am logged in as a user with the "editor" role
  And I am on "node/add/event"
  And I fill in "Title" with randomized text "New event"
  And I select the term "Addison Park" by id "edit-field-locations"

  # dept w/o events
  And I select the term "-Accounting" by id "edit-field-related-departments"
  And I fill in "Cost" with "free"
  And I fill in "edit-field-date-end-0-value-date" with "01/01/2050"
  And I open save options
  And I press "Save and Publish"

  When I am on "/departments/accounting"
  Then I should see randomized text "New event"

Scenario: Add department page to topic navigation
  Given I am logged in as a user with the "editor" role
  # computer services: an organization that doesn't have a topic
  And I am on "/node/476/edit"
  And I select the term "-Senior programs" by id "edit-field-lex-site-nav"
  And I open save options
  And I press "Save and Publish"

  When I am on "/browse/community-services/senior-programs"
  Then I should see the link "Computer Services"

  #Reset.
  When I am on "/node/476/edit"
  And I select the term "- None -" by id "edit-field-lex-site-nav"
  And I open save options
  And I press "Save and Publish"
  Then I should see "Home Government Departments and programs" in the breadcrumb region
