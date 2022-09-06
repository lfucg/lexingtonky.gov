<?php

namespace Drupal\Tests\search_api_solr\Kernel;

use Drupal\search_api\Entity\Server;
use Drupal\search_api_autocomplete\Entity\Search;

/**
 * Tests search autocomplete results using the Solr search backend.
 *
 * @group search_api_solr
 */
class SearchApiSolrAutocompleteTest extends SolrBackendTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'search_api_autocomplete',
  ];

  /**
   * {@inheritdoc}
   */
  public function testBackend() {
    $this->addTestEntity(1, [
      'name' => 'This is sparta',
      'body' => 'The scene originates from the 2006 film 300 directed by Zack Snyder.',
      'type' => 'article',
      'category' => 'movies',
    ]);

    $this->addTestEntity(2, [
      'name' => 'Queen',
      'body' => 'Queen are a British rock band formed in London in 1970.',
      'type' => 'article',
      'category' => 'music',
    ]);

    $this->addTestEntity(3, [
      'name' => 'William Shakespeare',
      'body' => 'Shakespeare produced most of his known works between 1589 and 1613.',
      'type' => 'article',
      'category' => 'actor',
    ]);

    $this->addTestEntity(4, [
      'name' => 'Fast and Furious',
      'body' => 'This article is about the Fast & Furious 1 media franchise.',
      'type' => 'article',
      'category' => 'movies',
    ]);

    $this->addTestEntity(5, [
      'name' => 'Fast and Furious 2',
      'body' => 'This article is about the Fast and Furious 2 media francia.',
      'type' => 'article',
      'category' => 'movies',
    ]);

    $this->indexItems($this->indexId);

    /** @var \Drupal\search_api_solr\Plugin\search_api\backend\SearchApiSolrBackend $backend */
    $backend = Server::load($this->serverId)->getBackend();
    $autocompleteSearch = new Search(['index_id' => $this->indexId], 'search_api_solr_autocomplete_search');

    $query = $this->buildSearch(['produc'], [], ['body_unstemmed'], FALSE);
    $query->setLanguages(['en']);
    $suggestions = $backend->getAutocompleteSuggestions($query, $autocompleteSearch, 'produc', 'produc');
    $this->assertEquals(1, count($suggestions));
    $this->assertEquals('ed', $suggestions[0]->getSuggestionSuffix());
    $this->assertEquals(1, $suggestions[0]->getResultsCount());

    $query = $this->buildSearch(['furi'], [], ['body'], FALSE);
    $query->setLanguages(['en']);
    $suggestions = $backend->getAutocompleteSuggestions($query, $autocompleteSearch, 'furi', 'furi');
    $this->assertEquals(1, count($suggestions));
    $this->assertEquals('ous', $suggestions[0]->getSuggestionSuffix());
    $this->assertEquals(2, $suggestions[0]->getResultsCount());

    $query = $this->buildSearch(['fast and fur'], [], ['body'], FALSE);
    $query->setLanguages(['en']);
    $suggestions = $backend->getAutocompleteSuggestions($query, $autocompleteSearch, 'fur', 'fast and fur');
    $this->assertEquals('fast and furious', $suggestions[0]->getSuggestedKeys());
    $this->assertEquals(2, $suggestions[0]->getResultsCount());

    $query = $this->buildSearch(['media fran'], [], ['body'], FALSE);
    $query->setLanguages(['en']);
    $suggestions = $backend->getAutocompleteSuggestions($query, $autocompleteSearch, 'fran', 'media fran');
    $this->assertEquals('media franchis', $suggestions[0]->getSuggestedKeys());
    $this->assertEquals(1, $suggestions[0]->getResultsCount());
    $this->assertEquals('media franchise.', $suggestions[1]->getSuggestedKeys());
    $this->assertEquals(1, $suggestions[1]->getResultsCount());
    $this->assertEquals('media francia', $suggestions[2]->getSuggestedKeys());
    $this->assertEquals(1, $suggestions[2]->getResultsCount());
    $this->assertEquals('media francia.', $suggestions[3]->getSuggestedKeys());
    $this->assertEquals(1, $suggestions[3]->getResultsCount());
  }

}
