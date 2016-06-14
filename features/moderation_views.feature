@api
Feature: Add and Manage service guide content for the 'Needs Review' rss-feed view

  Scenario: Review requests for new content appears in feed
    Given I am logged in as a user with the "authenticated user" role
    And I am on "/node/add/page"
    And I fill in "Title" with "apax"
    And I select "-Councilmembers" from "Navigation topic"
    And I press "Save and Create New Draft"
    And I am on "/content-needing-review.xml"
    Then the response should not contain "apax"

    And I am on "/apax"
    And I click "Edit draft"
    And I press "Save and Request Review"
    When I am on "/content-needing-review.xml"
    Then the response should contain "apax"

  Scenario: Forward revisions appear in feed (drafts made to published content)
    Given I am logged in as a user with the "editor" role
    And I am on "/node/add/page"
    And I fill in "Title" with "apaxsoftware"
    And I select "-Councilmembers" from "Navigation topic"
    And I press "Save and Publish"

    When I am not logged in
    And I am on "/apaxsoftware"
    Then the response status code should be 200

    Given I am logged in as a user with the "editor" role
    And I am on "/apaxsoftware"
    And I click "New draft"
    And I press "Save and Create New Draft"
    And I select "Request Review" from "Moderate"
    And I press "Apply"

    When I am on "/content-needing-review.xml"
    Then the response should contain "apaxsoftware"

    Given I am not logged in
    When I am on "/apaxsoftware"
    Then the response status code should be 200

  # Waiting on views support for forward revisions https://www.drupal.org/node/2702041
  # Scenario: Only requests for approval appear in the content dashboard
  #   Given I am logged in as a user with the "editor" role
  #   And I am on "/node/add/page"
  #   And I fill in "Title" with "apaxsoftware"
  #   And I select "-Councilmembers" from "Navigation topic"
  #   And I press "Save and Request Review"
  #   When I am on "/content-needing-review"
  #   Then the response should contain "apaxsoftware"
  #
  #   Given I am on "/apaxsoftware"
  #   And I click "Edit draft"
  #   And I press "Save and Publish"
  #   When I am on "/content-needing-review"
  #   Then the response should not contain "apaxsoftware"
  #
  #   Given I am on "/apaxsoftware"
  #   And I click "New draft"
  #   And I press "Save and Create New Draft"
  #   And I select "Request Review" from "Moderate"
  #   And I press "Apply"
  #   When I am on "/content-needing-review"
  #   Then the response should contain "apaxsoftware"
  #
  #   # Make sure still published
  #   Given I am not logged in
  #   When I am on "/apaxsoftware"
  #   Then the response status code should be 200
