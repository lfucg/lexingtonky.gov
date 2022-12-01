@api
Feature: Add and Manage service guide content for the 'Needs Review' rss-feed view

  Scenario: Review requests for new content appears in feed
    Given I am logged in as a user with the "authenticated user" role
    And I am on "/node/add/page"
    And I fill in "Title" with "apax"
    And I fill in "Navigation topic" with "Councilmembers"
#    And I select the term "-Councilmembers" by id "edit-field-lex-site-nav-0-target-id"
    And I open save options
    And I press "Save and Create New Draft"
    And I am on "/content-needing-review.xml"
    Then the response should not contain "apax"

    And I am on "/apax"
    And I click "Edit draft"
    And I open save options
    And I press "Save and Request Review"
    When I am on "/content-needing-review.xml"
    Then the response should contain "apax"

  Scenario: Forward revisions appear in feed (drafts made to published content)
    Given I am logged in as a user with the "editor" role
    And I am on "/node/add/page"
    And I fill in "Title" with "apaxsoftware"
    And I fill in "Navigation topic" with "Councilmembers"
#    And I select the term "-Councilmembers" by id "edit-field-lex-site-nav-0-target-id"
    And I open save options
    And I press "Save and Publish"

    When I am not logged in
    And I am on "/apaxsoftware"
    Then the response should contain "apaxsoftware"

    Given I am logged in as a user with the "editor" role
    And I am on "/apaxsoftware"
    And I click "New draft"
    And I open save options
    And I press "Save and Create New Draft"
    And I select "Request Review" from "Moderate"
    And I press "Apply"

    When I am on "/content-needing-review.xml"
    Then the response should contain "apaxsoftware"

    Given I am not logged in
    When I am on "/apaxsoftware"
    Then the response should contain "apaxsoftware"

  Scenario: Only requests for approval appear in the content dashboard
    Given I am logged in as a user with the "editor" role
    And I am on "/node/add/page"
    And I fill in "Title" with "apaxsoftware"
     And I fill in "Navigation topic" with "Councilmembers"
#   And I select the term "-Councilmembers" by id "edit-field-lex-site-nav"
    And I open save options
    And I press "Save and Request Review"
    When I am on "/content-needing-review"
    Then the response should contain "apaxsoftware"

    Given I am on "/apaxsoftware"
    And I click "Edit draft"
    And I open save options
    And I press "Save and Publish"
    When I am on "/content-needing-review"
    Then the response should not contain "apaxsoftware"

    Given I am on "/apaxsoftware"
    And I click "New draft"
    And I open save options
    And I press "Save and Create New Draft"
    And I select "Request Review" from "Moderate"
    And I press "Apply"
    When I am on "/content-needing-review"
    Then the response should contain "apaxsoftware"

    # Make sure still published
    Given I am not logged in
    When I am on "/apaxsoftware"
    Then the response should contain "apaxsoftware"
