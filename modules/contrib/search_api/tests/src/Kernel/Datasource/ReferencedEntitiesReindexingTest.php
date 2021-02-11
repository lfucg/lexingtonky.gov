<?php

declare(strict_types=1);

namespace Drupal\Tests\search_api\Kernel\Datasource;

use Drupal\KernelTests\KernelTestBase;
use Drupal\node\Entity\Node;
use Drupal\search_api\Entity\Index;
use Drupal\search_api\Entity\Server;
use Drupal\search_api\Utility\TrackingHelper;
use Drupal\search_api\Utility\Utility;

/**
 * Tests that changes in related entities are correctly tracked.
 *
 * @group search_api
 */
class ReferencedEntitiesReindexingTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'user',
    'node',
    'field',
    'system',
    'search_api',
    'search_api_test',
    'search_api_test_example_content_references',
  ];

  /**
   * The search index used for this test.
   *
   * @var \Drupal\search_api\IndexInterface
   */
  protected $index;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->installSchema('search_api', ['search_api_item']);
    $this->installSchema('node', ['node_access']);
    $this->installEntitySchema('user');
    $this->installEntitySchema('node');
    $this->installEntitySchema('search_api_task');
    $this->installConfig([
      'search_api',
      'search_api_test_example_content_references',
    ]);

    // Do not use a batch for tracking the initial items after creating an
    // index when running the tests via the GUI. Otherwise, it seems Drupal's
    // Batch API gets confused and the test fails.
    if (!Utility::isRunningInCli()) {
      \Drupal::state()->set('search_api_use_tracking_batch', FALSE);
    }

    Server::create([
      'id' => 'server',
      'backend' => 'search_api_test',
    ])->save();
    $this->index = Index::create([
      'id' => 'index',
      'tracker_settings' => [
        'default' => [],
      ],
      'datasource_settings' => [
        'entity:node' => [
          'bundles' => [
            'default' => FALSE,
            'selected' => ['parent'],
          ],
        ],
        'entity:user' => [],
      ],
      'server' => 'server',
      'field_settings' => [
        'child_indexed' => [
          'label' => 'Child > Indexed',
          'datasource_id' => 'entity:node',
          'property_path' => 'entity_reference:entity:indexed',
          'type' => 'text',
        ],
      ],
    ]);

    $this->index->save();
  }

  /**
   * Tests correct tracking of changes in referenced entities.
   *
   * @param array $parent_map
   *   Map of parent nodes to create. It should be compatible with the
   *   ::createEntitiesFromMap() method.
   * @param array $child_map
   *   Map of the child nodes to create. It should be compatible with the
   *   ::createEntitiesFromMap().
   * @param array $updates
   *   A list of updates to child entities to execute. It should be keyed by the
   *   machine-name of the child entities. Value can be either FALSE (to remove
   *   an entity) or a list of the new raw values to apply to the entity.
   * @param array $expected
   *   A list of search items that should be marked for reindexing.
   *
   * @dataProvider referencedEntityChangedDataProvider
   */
  public function testReferencedEntityChanged(array $parent_map, array $child_map, array $updates, array $expected) {
    $children = $this->createEntitiesFromMap($child_map, [], 'child');
    $this->createEntitiesFromMap($parent_map, $children, 'parent');

    $this->index->indexItems();
    $tracker = $this->index->getTrackerInstance();
    $this->assertEquals([], $tracker->getRemainingItems());

    // Now let's execute updates.
    foreach ($updates as $i => $field_updates) {
      if ($field_updates === FALSE) {
        $children[$i]->delete();
      }
      else {
        foreach ($field_updates as $field => $items) {
          $children[$i]->get($field)->setValue($items);
        }

        $children[$i]->save();
      }
    }

    $this->assertEquals($expected, $tracker->getRemainingItems());
  }

  /**
   * Provides test data for testReferencedEntityChanged().
   *
   * @return array[]
   *   An array of argument arrays for testReferencedEntityChanged().
   *
   * @see \Drupal\Tests\search_api\Kernel\ReferencedEntitiesReindexingTest::testReferencedEntityChanged()
   */
  public function referencedEntityChangedDataProvider(): array {
    $tests = [];

    $parent_map = [
      'parent' => [
        'title' => 'Parent',
        'entity_reference' => 'child',
      ],
    ];

    $parent_expected = ['entity:node/3:en'];

    $child_variants = ['child', 'unrelated'];
    $field_variants = ['indexed', 'not_indexed'];

    foreach ($child_variants as $child) {
      foreach ($field_variants as $field) {
        if ($child == 'child' && $field == 'indexed') {
          // This is how Search API represents our "parent" node in its tracking
          // data.
          $expected = $parent_expected;
        }
        else {
          $expected = [];
        }

        $tests["changing value of $field field within the $child entity"] = [
          $parent_map,
          [
            'child' => [
              'title' => 'Child',
              'indexed' => 'Original indexed value',
              'not_indexed' => 'Original not indexed value.',
            ],
            'unrelated' => [
              'title' => 'Unrelated child',
              'indexed' => 'Original indexed value',
              'not_indexed' => 'Original not indexed value.',
            ],
          ],
          [
            $child => [
              $field => "New $field value.",
            ],
          ],
          $expected,
        ];

        $tests["appending value of $field field within the $child entity"] = [
          $parent_map,
          [
            'child' => [
              'title' => 'Child',
              'indexed' => 'Original indexed value',
            ],
            'unrelated' => [
              'title' => 'Unrelated child',
              'indexed' => 'Original indexed value',
            ],
          ],
          [
            $child => [
              $field => "New $field value.",
            ],
          ],
          $expected,
        ];

        $tests["removing value of $field field within the $child entity"] = [
          $parent_map,
          [
            'child' => [
              'title' => 'Child',
              'indexed' => 'Original indexed value',
              'not_indexed' => 'Original not indexed value.',
            ],
            'unrelated' => [
              'title' => 'Unrelated child',
              'indexed' => 'Original indexed value',
              'not_indexed' => 'Original not indexed value.',
            ],
          ],
          [
            $child => [
              $field => [],
            ],
          ],
          $expected,
        ];
      }

      $tests["removing the $child entity"] = [
        $parent_map,
        [
          'child' => [
            'title' => 'Child',
            'indexed' => 'Original indexed value',
            'not_indexed' => 'Original not indexed value.',
          ],
          'unrelated' => [
            'title' => 'Unrelated child',
            'indexed' => 'Original indexed value',
            'not_indexed' => 'Original not indexed value.',
          ],
        ],
        [
          $child => FALSE,
        ],
        $child == 'child' ? $parent_expected : [],
      ];
    }

    return $tests;
  }

  /**
   * Creates a list of entities with the given fields.
   *
   * @param array[] $entity_fields
   *   Map of entities to create. It should be keyed by a machine-friendly name.
   *   Values of this map should be sub-arrays that represent raw values to
   *   supply into the entity's fields when creating it.
   * @param \Drupal\Core\Entity\ContentEntityInterface[] $references_map
   *   There is a magical field "entity_reference" in the $map input argument.
   *   Values of this field should reference some other entity. This "other"
   *   entity will be looked up by the key in this references map. This way you
   *   can create entity reference data without knowing the entity IDs ahead of
   *   time.
   * @param string $bundle
   *   Bundle to utilize when creating entities from the $map array.
   *
   * @return \Drupal\Core\Entity\ContentEntityInterface[]
   *   Entities created according to the supplied $map array. This array will be
   *   keyed by the same machine-names as the input $map argument.
   */
  protected function createEntitiesFromMap(array $entity_fields, array $references_map, string $bundle): array {
    $entities = [];

    foreach ($entity_fields as $i => $fields) {
      if (isset($fields['entity_reference'])) {
        $fields['entity_reference'] = $references_map[$fields['entity_reference']]->id();
      }
      $fields['type'] = $bundle;
      $entities[$i] = Node::create($fields);
      $entities[$i]->save();
    }

    return $entities;
  }

  /**
   * Tests whether relationships are correctly separated between datasources.
   *
   * @see https://www.drupal.org/node/3178941
   */
  public function testUnrelatedDatasourceUnaffected() {
    // First, check whether the tracking helper correctly includes "datasource"
    // keys with all foreign relationship entries.
    $tracking_helper = \Drupal::getContainer()
      ->get('search_api.tracking_helper');
    $method = new \ReflectionMethod(TrackingHelper::class, 'getForeignEntityRelationsMap');
    $method->setAccessible(TRUE);
    /** @see \Drupal\search_api\Utility\TrackingHelper::getForeignEntityRelationsMap() */
    $map = $method->invoke($tracking_helper, $this->index);
    $expected = [
      [
        'datasource' => 'entity:node',
        'entity_type' => 'node',
        // Note: It's unspecified that this array has string keys, only its
        // values are important. Still, it's easier to just reflect the current
        // implementation, when checking for equality.
        'bundles' => ['child' => 'child'],
        'property_path_to_foreign_entity' => 'entity_reference',
        'field_name' => 'indexed',
      ],
    ];
    $this->assertEquals($expected, $map);

    // Then, check whether datasources correctly ignore relationships from other
    // datasources, or that they at least don't lead to an exception/error.
    $datasource = $this->index->getDatasource('entity:user');
    $entities = $this->createEntitiesFromMap([
      'child' => [
        'title' => 'Child',
        'indexed' => 'Indexed value',
        'not_indexed' => 'Not indexed value.',
      ],
    ], [], 'child');
    $child = reset($entities);
    $original_child = clone $child;
    $child->get('indexed')->setValue(['New value']);
    $result = $datasource->getAffectedItemsForEntityChange($child, $map, $original_child);
    $this->assertEquals([], $result);

    // Change foreign relationships map slightly to trigger #3178941 on purpose.
    $map[0]['property_path_to_foreign_entity'] = 'entity_reference:entity';
    $result = $datasource->getAffectedItemsForEntityChange($child, $map, $original_child);
    $this->assertEquals([], $result);
  }

}
