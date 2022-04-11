<?php

namespace Drupal\Tests\search_api_solr\Kernel;

use Drupal\config_test\TestInstallStorage;
use Drupal\Core\Config\InstallStorage;
use Drupal\Core\Config\TypedConfigManager;
use Drupal\KernelTests\KernelTestBase;
use Drupal\language\Entity\ConfigurableLanguage;
use Drupal\Tests\SchemaCheckTestTrait;

/**
 * Provides tests for Solr field typa configs.
 *
 * @group search_api_solr
 */
class SolrFieldTypeTest extends KernelTestBase {

  use SchemaCheckTestTrait;

  /**
   * Solr field type config names.
   *
   * @var array
   */
  protected $configNames = [];

  /**
   * Languages covered by Solr field type configs.
   *
   * @var array
   */
  protected $languageIds = [];

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'language',
    'search_api',
    'search_api_solr',
    'user',
  ];

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
    parent::setUp();

    $fileSystem = \Drupal::service('file_system');
    $this->configNames = array_keys(\Drupal::service('file_system')->scanDirectory(__DIR__ . '/../../../config', '/search_api_solr.solr_field_type.text_/', ['key' => 'name']));
    foreach ($this->configNames as $config_name) {
      preg_match('/search_api_solr.solr_field_type.text_(.*)_\d+_\d+_\d+/', $config_name, $matches);
      $this->languageIds[] = $matches[1];
    }
    $this->languageIds = array_unique($this->languageIds);

    foreach ($this->languageIds as $language_id) {
      if ('und' != $language_id) {
        ConfigurableLanguage::createFromLangcode($language_id)->save();
      }
    }
  }

  /**
   * Tests all available Solr field type configs.
   */
  public function testDefaultConfig() {
    // Create a typed config manager with access to configuration schema in
    // every module, profile and theme.
    $typed_config = new TypedConfigManager(
      \Drupal::service('config.storage'),
      new TestInstallStorage(InstallStorage::CONFIG_SCHEMA_DIRECTORY),
      \Drupal::service('cache.discovery'),
      \Drupal::service('module_handler')
    );

    // Create a configuration storage with access to default configuration in
    // every module, profile and theme.
    $default_config_storage = new TestInstallStorage('test_search_api_solr_multilingual');

    foreach ($this->configNames as $config_name) {
      $data = $default_config_storage->read($config_name);
      $this->assertConfigSchema($typed_config, $config_name, $data);
    }
  }

}
