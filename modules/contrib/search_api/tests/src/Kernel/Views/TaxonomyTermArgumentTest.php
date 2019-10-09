<?php

namespace Drupal\Tests\search_api\Kernel\Views;

use Drupal\KernelTests\KernelTestBase;
use Drupal\search_api\Plugin\views\argument\SearchApiTerm;
use Drupal\Tests\taxonomy\Functional\TaxonomyTestTrait;

/**
 * Tests whether the SearchApiTerm plugin works correctly.
 *
 * @group search_api
 */
class TaxonomyTermArgumentTest extends KernelTestBase {

  use TaxonomyTestTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'filter',
    'taxonomy',
    'text',
    'user',
  ];

  /**
   * The test vocabulary.
   *
   * @var \Drupal\taxonomy\Entity\Vocabulary
   */
  protected $vocabulary;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->vocabulary = $this->createVocabulary();
    $this->installEntitySchema('taxonomy_term');
    $this->installConfig(['filter']);
  }

  /**
   * Tests that null is returned if no argument has been set for any reason.
   */
  public function testReturnsNullIfArgumentNotSet() {
    $plugin = $this->getSubjectUnderTest();

    $this->assertNull($plugin->title());
  }

  /**
   * Tests hat the set argument is returned when no value is provided.
   */
  public function testReturnsArgumentIfSet() {
    $plugin = $this->getSubjectUnderTest('argument');

    $this->assertEquals('argument', $plugin->title());
  }

  /**
   * Tests that the set argument is returned when non existing ids are provided.
   */
  public function testReturnsArgumentIfInvalidTermIdIsPassed() {
    $plugin = $this->getSubjectUnderTest('argument');

    $term = $this->createTerm($this->vocabulary);
    $non_existing_term_id = $term->id() + 1;
    $plugin->value = [$non_existing_term_id];

    $this->assertEquals('argument', $plugin->title());
  }

  /**
   * Tests that the term label is returned if an existing id is provided.
   */
  public function testReturnsTermNameIfValidTermIdIsPassed() {
    $plugin = $this->getSubjectUnderTest('argument');

    $term = $this->createTerm($this->vocabulary);
    $plugin->value = [$term->id()];

    $this->assertEquals($term->label(), $plugin->title());
  }

  /**
   * Tests that a comma separated list of term labels is returned.
   */
  public function testReturnsCommaSeparatedNamesIfValidTermIdsArePassed() {
    $plugin = $this->getSubjectUnderTest('argument');

    $term1 = $this->createTerm($this->vocabulary);
    $term2 = $this->createTerm($this->vocabulary);
    $plugin->value = [$term1->id(), $term2->id()];

    $this->assertEquals("{$term1->label()}, {$term2->label()}", $plugin->title());
  }

  /**
   * Creates the plugin to test.
   *
   * @param string|null $argument
   *   The argument to set on the plugin.
   *
   * @return \Drupal\search_api\Plugin\views\argument\SearchApiTerm
   *   The subject under test.
   */
  protected function getSubjectUnderTest($argument = NULL) {
    $plugin = new SearchApiTerm([], 'search_api_term', []);
    if ($argument !== NULL) {
      $plugin->argument_validated = TRUE;
      $plugin->setArgument($argument);
    }
    return $plugin;
  }

}
