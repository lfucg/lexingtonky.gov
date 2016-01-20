<?php

/**
 * @file
 * Contains \Drupal\Tests\contact_storage\Kernel\ContactStorageFieldTest.
 */

namespace Drupal\Tests\contact_storage\Kernel;
use Drupal\KernelTests\KernelTestBase;


/**
 * Tests contact_storage ID field.
 * @group contact_storage
 */
class ContactStorageFieldTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = ['contact_storage', 'contact', 'user'];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->installEntitySchema('contact_message');
  }

  /**
   * Covers contact_storage_install().
   */
  public function testContactIdFieldIsCreated() {
    // There should be no updates as contact_storage_install() should have
    // applied the new field.
    $this->assertTrue(empty($this->container->get('entity.definition_update_manager')->needsUpdates()['contact_message']));
    $this->assertTrue(!empty($this->container->get('entity_field.manager')->getFieldStorageDefinitions('contact_message')['id']));
  }

}
