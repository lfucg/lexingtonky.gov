Feature: Solr on Pantheon
# This feature file is not a representation of Behavior Driven Development.
# It is a series of simulated clicks and form interactions.

  @api
  Scenario: Create Solr server configuration
    Given I am logged in as a user with the "administrator" role
    When I visit "admin/config/search/search-api/add-server"
    And I fill in "name" with "pantheon"
    And I fill in "id" with "pantheon"
    # On 2017-02-08, this step stopped working when additional description text
    # started to display on the form. Because "Solr" is already selected
    # (and the only option for the field) it is not necessary.
    # And I select the radio button "Solr"
    And I select the radio button "Pantheon"
    And I press the "Save" button
    And I select the radio button "modules/contrib/search_api_solr/solr-conf/4.x/schema.xml"
    And I press the "Save" button

    # Here is the real verification of this scenario, that the server can be
    # reached.
    Then I should see "The Solr server could be reached"
    Then I should see "The Solr core could be accessed (latency: "

  @api
  Scenario: Create Solr index configuration, index the title field.
    Given I am logged in as a user with the "administrator" role
    When I visit "admin/config/search/search-api/add-index"
    And I fill in "name" with "nodes"
    And I fill in "id" with "nodes"
    And I check "Content"
    And I select the radio button "pantheon"
    And I press the "Save" button
    # Without JavaScript, The first pressing of the save button will redirect
    # back to the same form (with more options available, which aren't needed for
    # this test.)
    And I press the "Save" button
    When I visit "admin/config/search/search-api/index/nodes/fields/add/nojs"
    And I press the "entity:node/title" button
    When I visit "admin/config/search/search-api/index/nodes/fields"
    And I select "Fulltext" from "fields[title][type]"
    And I press the "Save" button

  @api
  Scenario: Create Search page
    Given I am logged in as a user with the "administrator" role
    When I visit "admin/config/search/search-api-pages/add"
    And I fill in "label" with "content-search"
    And I fill in "id" with "content_search"
    And I select "nodes" from "index"
    And I press the "Next" button
    When I visit "admin/config/search/search-api-pages/content_search"
    And I select "title" from "searched_fields[]"
    And I fill in "path" with "content-search"
    And I press the "Save" button

  @api
  Scenario: Create a test node, fill search index, search for node.
  # Behat will delete this node at the end of the run.
    Given I am logged in as a user with the "administrator" role
    When I visit "node/add/article"
    And I fill in "title[0][value]" with "Test article title"
    And I press "Save"
    When I visit "admin/content"
    Then I should see the text "Test article"
    When I visit "admin/reports/status"
    And I follow "Run cron"
    When I visit "admin/config/search/search-api/index/nodes"
    Then I should see "100%" in the "index_percentage" region
    # And I break
    When I visit "content-search"
    And I fill in "keys" with "Test article"
    And I press the "Search" button
    Then I should see the link "Test article title"
    Then I should not see the text "Your search yielded no results."
