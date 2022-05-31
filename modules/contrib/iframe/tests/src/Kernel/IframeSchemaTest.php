<?php

namespace Drupal\Tests\iframe\Kernel;

use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\KernelTests\Core\Entity\EntityKernelTestBase;
use Drupal\Tests\SchemaCheckTestTrait;

/**
 * Ensures that Iframe schema is correct.
 *
 * @group iframe
 */
class IframeSchemaTest extends EntityKernelTestBase {

  use SchemaCheckTestTrait;

  /**
   * Modules to install.
   *
   * @var array
   */
  public static $modules = [
    'iframe',
    'link',
  ];

  /**
   * Tests Iframe schema.
   */
  public function testIframeSchema() {
    // Please note viewing this in iframe using web browser doesnt actually
    // work. We're simply testing things here. If you want to test something
    // you can use "/" to load the homepage.
    $url = 'https://www.drupal.org/';
    $typed_config = \Drupal::service('config.typed');
    // Test the field storage schema.
    /** @var \Drupal\field\Entity\FieldStorageConfig $field */
    $field_storage = FieldStorageConfig::create([
      'entity_type' => 'entity_test',
      'field_name' => 'field_test',
      'type' => 'iframe',
      'cardinality' => FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED,
    ]);
    $field_storage->save();

    $this->assertConfigSchema($typed_config, 'field.storage.' . $field_storage->id(), $field_storage->toArray());

    // Test the field schema.
    /** @var \Drupal\field\Entity\FieldConfig $field */
    $field = FieldConfig::create([
      'entity_type' => 'entity_test',
      'field_name' => 'field_test',
      'bundle' => 'entity_test',
    ]);
    $entity_storage = \Drupal::entityTypeManager()->getStorage('entity_test');
    $field->setDefaultValue([
      [
        'title' => 'Iframe title',
        'headerlevel' => '3',
        'class' => 'iframe-class',
        'height' => '768',
        'width' => '1024',
        'frameborder' => '0',
        'scrolling' => 'auto',
        'transparency' => '0',
        'tokensupport' => '0',
        'allowfullscreen' => '0',
        'url' => $url,
      ],
    ]);
    $field->save();

    $this->assertConfigSchema($typed_config, 'field.field.' . $field->id(), $field->toArray());

    // Test the field widget schema.
    /** @var \Drupal\Core\Entity\Entity\EntityFormDisplay $form_display */
    $form_display = EntityFormDisplay::create([
      'targetEntityType' => 'entity_test',
      'bundle' => 'entity_test',
      'mode' => 'default',
      'status' => TRUE,
    ]);

    // Test schema of IframeUrlwidthheightWidget widget.
    $form_display->setComponent('field_test', [
      'weight' => 0,
      'type' => 'iframe_urlwidthheight',
      'settings' => [
        'width' => '1024',
        'height' => '768',
        'headerlevel' => '3',
        'class' => 'iframe-class',
        'expose_class' => 0,
        'frameborder' => '0',
        'scrolling' => 'auto',
        'transparency' => '0',
        'tokensupport' => '0',
        'allowfullscreen' => '0',
      ],
      'third_party_settings' => [],
    ])->save();

    $this->assertConfigSchema($typed_config, 'core.entity_form_display.' . $form_display->id(), $form_display->toArray());

    // Test schema of IframeUrlheightWidget widget.
    $form_display->setComponent('field_test', [
      'weight' => 0,
      'type' => 'iframe_urlheight',
      'settings' => [
        'width' => '1024',
        'height' => '768',
        'class' => 'iframe-class',
        'expose_class' => 0,
        'frameborder' => '0',
        'scrolling' => 'auto',
        'transparency' => '0',
        'tokensupport' => '0',
        'allowfullscreen' => '0',
      ],
      'third_party_settings' => [],
    ])->save();

    $this->assertConfigSchema($typed_config, 'core.entity_form_display.' . $form_display->id(), $form_display->toArray());

    // Test schema of IframeUrlWidget widget.
    $form_display->setComponent('field_test', [
      'weight' => 0,
      'type' => 'iframe_url',
      'settings' => [
        'width' => '1024',
        'height' => '768',
        'headerlevel' => '3',
        'class' => 'iframe-class',
        'expose_class' => 0,
        'frameborder' => '0',
        'scrolling' => 'auto',
        'transparency' => '0',
        'tokensupport' => '0',
        'allowfullscreen' => '0',
      ],
      'third_party_settings' => [],
    ])->save();

    $this->assertConfigSchema($typed_config, 'core.entity_form_display.' . $form_display->id(), $form_display->toArray());

    // Test all the field formatters schema.
    /** @var \Drupal\Core\Entity\Entity\EntityViewDisplay $view_display */
    $view_display = EntityViewDisplay::create([
      'targetEntityType' => 'entity_test',
      'bundle' => 'entity_test',
      'mode' => 'default',
      'status' => TRUE,
    ]);

    // Test schema of IframeDefaultFormatter widget.
    $view_display->setComponent('field_test', [
      'weight' => 0,
      'type' => 'iframe_default',
      'label' => 'above',
      'settings' => [
        'url' => '',
        'title' => '',
        'headerlevel' => '3',
        'width' => '',
        'height' => '',
        'class' => '',
        'frameborder' => '0',
        'scrolling' => '',
        'transparency' => '0',
        'tokensupport' => '0',
        'allowfullscreen' => '0',
      ],
      'third_party_settings' => [],
    ])->save();

    $this->assertConfigSchema($typed_config, 'core.entity_view_display.' . $view_display->id(), $view_display->toArray());

    // Test schema of IframeAsurlFormatter widget.
    $view_display->setComponent('field_test', [
      'weight' => 0,
      'type' => 'iframe_asurl',
      'label' => 'above',
      'settings' => [
        'url' => '',
        'title' => '',
        'width' => '',
        'height' => '',
        'class' => '',
        'frameborder' => '0',
        'scrolling' => '',
        'transparency' => '0',
        'tokensupport' => '0',
        'allowfullscreen' => '0',
      ],
      'third_party_settings' => [],
    ])->save();

    $this->assertConfigSchema($typed_config, 'core.entity_view_display.' . $view_display->id(), $view_display->toArray());

    // Test schema of IframeAsurlwithuriFormatter widget.
    $view_display->setComponent('field_test', [
      'weight' => 0,
      'type' => 'iframe_asurlwithuri',
      'label' => 'above',
      'settings' => [
        'url' => '',
        'title' => '',
        'headerlevel' => '3',
        'width' => '',
        'height' => '',
        'class' => '',
        'frameborder' => '0',
        'scrolling' => '',
        'transparency' => '0',
        'tokensupport' => '0',
        'allowfullscreen' => '0',
      ],
      'third_party_settings' => [],
    ])->save();

    $this->assertConfigSchema($typed_config, 'core.entity_view_display.' . $view_display->id(), $view_display->toArray());

    // Test schema of IframeOnlyFormatter widget.
    $view_display->setComponent('field_test', [
      'weight' => 0,
      'type' => 'iframe_only',
      'label' => 'above',
      'settings' => [
        'url' => '',
        'title' => '',
        'headerlevel' => '3',
        'width' => '',
        'height' => '',
        'class' => '',
        'frameborder' => '0',
        'scrolling' => '',
        'transparency' => '0',
        'tokensupport' => '0',
        'allowfullscreen' => '0',
      ],
      'third_party_settings' => [],
    ])->save();

    $this->assertConfigSchema($typed_config, 'core.entity_view_display.' . $view_display->id(), $view_display->toArray());

  }

}
