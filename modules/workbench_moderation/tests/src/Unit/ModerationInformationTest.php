<?php

namespace Drupal\Tests\workbench_moderation\Unit;

use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\Core\Entity\ContentEntityFormInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\ContentEntityType;
use Drupal\Core\Entity\EntityFormInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\workbench_moderation\ModerationInformation;

/**
 * @coversDefaultClass \Drupal\workbench_moderation\ModerationInformation
 * @group workbench_moderation
 */
class ModerationInformationTest extends \PHPUnit_Framework_TestCase {

  /**
   * Builds a mock user.
   *
   * @return AccountInterface
   */
  protected function getUser() {
    return $this->prophesize(AccountInterface::class)->reveal();
  }

  /**
   * Returns a mock Entity Type Manager.
   *
   * @param \Drupal\Core\Entity\EntityStorageInterface $entity_bundle_storage
   *
   * @return EntityTypeManagerInterface
   */
  protected function getEntityTypeManager(EntityStorageInterface $entity_bundle_storage) {
    $entity_type_manager = $this->prophesize(EntityTypeManagerInterface::class);
    $entity_type_manager->getStorage('entity_test_bundle')->willReturn($entity_bundle_storage);
    return $entity_type_manager->reveal();
  }

  public function setupModerationEntityManager($status) {
    $bundle = $this->prophesize(ConfigEntityInterface::class);
    $bundle->getThirdPartySetting('workbench_moderation', 'enabled', FALSE)->willReturn($status);

    $entity_storage = $this->prophesize(EntityStorageInterface::class);
    $entity_storage->load('test_bundle')->willReturn($bundle->reveal());

    return $this->getEntityTypeManager($entity_storage->reveal());
  }

  /**
   * @dataProvider providerBoolean
   * @covers ::isModeratableEntity
   */
  public function testIsModeratableEntity($status) {
    $moderation_information = new ModerationInformation($this->setupModerationEntityManager($status), $this->getUser());

    $entity_type = new ContentEntityType([
      'id' => 'test_entity_type',
      'bundle_entity_type' => 'entity_test_bundle',
    ]);
    $entity = $this->prophesize(ContentEntityInterface::class);
    $entity->getEntityType()->willReturn($entity_type);
    $entity->bundle()->willReturn('test_bundle');

    $this->assertEquals($status, $moderation_information->isModeratableEntity($entity->reveal()));
  }

  /**
   * @covers ::isModeratableEntity
   */
  public function testIsModeratableEntityForNonBundleEntityType() {
    $entity_type = new ContentEntityType([
      'id' => 'test_entity_type',
    ]);
    $entity = $this->prophesize(ContentEntityInterface::class);
    $entity->getEntityType()->willReturn($entity_type);
    $entity->bundle()->willReturn('test_entity_type');

    $entity_storage = $this->prophesize(EntityStorageInterface::class);
    $entity_type_manager = $this->getEntityTypeManager($entity_storage->reveal());
    $moderation_information = new ModerationInformation($entity_type_manager, $this->getUser());

    $this->assertEquals(FALSE, $moderation_information->isModeratableEntity($entity->reveal()));
  }

  /**
   * @dataProvider providerBoolean
   * @covers ::isModeratableBundle
   */
  public function testIsModeratableBundle($status) {
    $entity_type = new ContentEntityType([
      'id' => 'test_entity_type',
      'bundle_entity_type' => 'entity_test_bundle',
    ]);

    $moderation_information = new ModerationInformation($this->setupModerationEntityManager($status), $this->getUser());

    $this->assertEquals($status, $moderation_information->isModeratableBundle($entity_type, 'test_bundle'));
  }

  /**
   * @dataProvider providerBoolean
   * @covers ::isModeratedEntityForm
   */
  public function testIsModeratedEntityForm($status) {
    $entity_type = new ContentEntityType([
      'id' => 'test_entity_type',
      'bundle_entity_type' => 'entity_test_bundle',
    ]);

    $entity = $this->prophesize(ContentEntityInterface::class);
    $entity->getEntityType()->willReturn($entity_type);
    $entity->bundle()->willReturn('test_bundle');

    $form = $this->prophesize(ContentEntityFormInterface::class);
    $form->getEntity()->willReturn($entity);

    $moderation_information = new ModerationInformation($this->setupModerationEntityManager($status), $this->getUser());

    $this->assertEquals($status, $moderation_information->isModeratedEntityForm($form->reveal()));
  }

  public function testIsModeratedEntityFormWithNonContentEntityForm() {
    $form = $this->prophesize(EntityFormInterface::class);
    $moderation_information = new ModerationInformation($this->setupModerationEntityManager(TRUE), $this->getUser());

    $this->assertFalse($moderation_information->isModeratedEntityForm($form->reveal()));
  }

  public function providerBoolean() {
    return [
      [FALSE],
      [TRUE],
    ];
  }

}
