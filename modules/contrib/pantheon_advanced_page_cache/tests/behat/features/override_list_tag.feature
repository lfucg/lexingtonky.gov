Feature: Listing tags
In order to control caching of lists
As an administrator
I want to toggle the setting for overriding cache tags

  @api
  Scenario: Core behavior
    Given there are some "article" nodes
    And "/" is caching
    When a generate a "article" node
    Then "/" has been purged
    And "/" is caching

  @api @current
  Scenario: Old override
    Given there are some "article" nodes
    When I run drush "config:set pantheon_advanced_page_cache.settings --input-format=yaml   override_list_tags true"
    And "/" is caching
    When a generate a "article" node
    And "/" has not been purged

