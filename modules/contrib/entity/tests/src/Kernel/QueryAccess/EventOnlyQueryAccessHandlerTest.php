<?php

namespace Drupal\Tests\entity\Kernel\QueryAccess;

use Drupal\KernelTests\Core\Entity\EntityKernelTestBase;

/**
 * Tests the generic query access handler.
 *
 * @coversDefaultClass \Drupal\entity\QueryAccess\EventOnlyQueryAccessHandler
 * @group entity
 */
class EventOnlyQueryAccessHandlerTest extends EntityKernelTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'entity',
    'entity_module_test',
    'node',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->installEntitySchema('node');

    // Create uid: 1 here so that it's skipped in test cases.
    $admin_user = $this->createUser();
  }

  /**
   * Tests that entity types without a query access handler still fire events.
   */
  public function testEventOnlyQueryAccessHandlerEventSubscriber() {
    \Drupal::state()->set('test_event_only_query_access', TRUE);

    $node_type_storage = $this->entityTypeManager->getStorage('node_type');
    $node_type_storage->create(['type' => 'foo', 'name' => $this->randomString()])->save();
    $node_type_storage->create(['type' => 'bar', 'name' => $this->randomString()])->save();

    $node_storage = $this->entityTypeManager->getStorage('node');
    $node_1 = $node_storage->create(['type' => 'foo', 'title' => $this->randomString()]);
    $node_1->save();
    $node_2 = $node_storage->create(['type' => 'bar', 'title' => $this->randomString()]);
    $node_2->save();

    $unfiltered = $node_storage->getQuery()->accessCheck(FALSE)->execute();
    $this->assertCount(2, $unfiltered, 'Both nodes show up when access checking is turned off.');
    $this->assertArrayHasKey($node_1->id(), $unfiltered, 'foo nodes were not filtered out.');
    $this->assertArrayHasKey($node_2->id(), $unfiltered, 'bar nodes were not filtered out.');

    $filtered = $node_storage->getQuery()->execute();
    $this->assertCount(1, $filtered, 'Only one node shows up when access checking is turned on.');
    $this->assertArrayHasKey($node_1->id(), $filtered, 'foo nodes were not filtered out.');
    $this->assertArrayNotHasKey($node_2->id(), $filtered, 'bar nodes were filtered out.');
  }

}
