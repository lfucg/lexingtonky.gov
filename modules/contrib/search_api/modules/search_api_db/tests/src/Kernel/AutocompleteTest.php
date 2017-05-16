<?php

namespace Drupal\Tests\search_api_db\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\search_api_autocomplete\Entity\SearchApiAutocompleteSearch;
use Drupal\search_api_db\Plugin\search_api\backend\Database;
use Drupal\Tests\search_api\Functional\ExampleContentTrait;

/**
 * Tests autocomplete functionality of the Database backend.
 *
 * @requires module search_api_autocomplete
 * @coversDefaultClass \Drupal\search_api_db\Plugin\search_api\backend\Database
 * @group search_api
 */
abstract class AutocompleteTest extends KernelTestBase {

  use ExampleContentTrait;

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'entity_test',
    'field',
    'system',
    'text',
    'user',
    'search_api',
    'search_api_autocomplete',
    'search_api_db',
    'search_api_db_test_autocomplete',
    'search_api_test_db',
    'search_api_test_example_content',
  ];

  /**
   * A search server ID.
   *
   * @var string
   */
  protected $serverId = 'database_search_server';

  /**
   * A search index ID.
   *
   * @var string
   */
  protected $indexId = 'database_search_index';

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->installSchema('search_api', ['search_api_item']);
    $this->installSchema('system', ['router']);
    $this->installSchema('user', ['users_data']);
    $this->installEntitySchema('entity_test_mulrev_changed');
    $this->installEntitySchema('search_api_task');

    // Set the tracking page size so tracking will work properly.
    \Drupal::configFactory()
      ->getEditable('search_api.settings')
      ->set('tracking_page_size', 100)
      ->save();

    // Do not use a batch for tracking the initial items after creating an
    // index when running the tests via the GUI. Otherwise, it seems Drupal's
    // Batch API gets confused and the test fails.
    if (php_sapi_name() != 'cli') {
      \Drupal::state()->set('search_api_use_tracking_batch', FALSE);
    }

    $this->installConfig([
      'search_api_db',
      'search_api_test_example_content',
      'search_api_test_db',
      'search_api_db_test_autocomplete',
    ]);

    $this->setUpExampleStructure();
    $this->insertExampleContent();

    $this->indexItems($this->indexId);
  }

  /**
   * Tests whether autocomplete suggestions are correctly created.
   *
   * @covers ::getAutocompleteSuggestions
   */
  public function testAutocompletion() {
    $autocomplete = SearchApiAutocompleteSearch::load('search_api_views_search_api_test_view');
    $index = $autocomplete->getIndexInstance();
    /** @var \Drupal\search_api_db\Plugin\search_api\backend\Database $backend */
    $backend = $index->getServerInstance()->getBackend();

    $this->assertInstanceOf(Database::class, $backend);

    $query = $index->query()
      ->range(0, 10);
    $suggestions = $backend->getAutocompleteSuggestions($query, $autocomplete, 'fo', 'fo');
    $expected = [
      'foo' => 4,
      'foobar' => 1,
      'foobaz' => 1,
      'foobuz' => 1,
    ];
    $this->assertSuggestionsEqual($expected, $suggestions);

    $query = $index->query()
      ->keys('foo')
      ->range(0, 10);
    $suggestions = $backend->getAutocompleteSuggestions($query, $autocomplete, 'fo', 'foo fo');
    $expected = [
      'foo foobaz' => 1,
      'foo foobuz' => 1,
    ];
    $this->assertSuggestionsEqual($expected, $suggestions);
  }

  /**
   * Asserts that the returned suggestions are as expected.
   *
   * @param int[] $expected
   *   Associative array mapping suggestion strings to their counts.
   * @param \Drupal\search_api_autocomplete\SuggestionInterface[] $suggestions
   *   The suggestions returned by the backend.
   */
  protected function assertSuggestionsEqual(array $expected, array $suggestions) {
    $terms = [];
    foreach ($suggestions as $suggestion) {
      $keys = $suggestion->getKeys();
      if ($keys === NULL) {
        $keys = $suggestion->getSuggestionPrefix();
        $keys .= $suggestion->getUserInput();
        $keys .= $suggestion->getSuggestionSuffix();
      }
      $terms[$keys] = $suggestion->getResults();
    }
    $this->assertEquals($expected, $terms);
  }

}
