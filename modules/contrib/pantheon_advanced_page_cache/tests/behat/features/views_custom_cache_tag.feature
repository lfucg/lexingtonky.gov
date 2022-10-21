Feature: Views custom cache tags
In order to keep as many pages cached as possible when content is updated
As an administrator
I want to use granular cache tags for Views that reflect the type of content displayed by the View.

  @api
  Scenario: Node type-based expiration
    Given there are some "page" nodes
    And "custom-cache-tags/page" is caching
    And there are some "article" nodes
    And "custom-cache-tags/article" is caching

    # When I make a new page node
    When a generate a "page" node
    # Then the page listing is cleared the article is not
    Then "custom-cache-tags/page" has been purged
    And "custom-cache-tags/page" is caching
    And "custom-cache-tags/article" has not been purged

    # @todo, add scenario for demo module being turned off
    # @todo, check actual surrogate key header.
