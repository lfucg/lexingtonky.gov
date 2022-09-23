<?php

namespace Drupal\ckeditor_media_embed;

use Drupal\Core\Plugin\PluginBase;
use Drupal\ckeditor\CKEditorPluginInterface;
use Drupal\Core\Asset\LibraryDiscoveryInterface;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines a utlity plugin base for a CKEditor version aware plugin.
 */
abstract class CKEditorVersionAwarePluginBase extends PluginBase implements CKEditorPluginInterface, ContainerFactoryPluginInterface {

  /**
   * The library discovery service.
   *
   * @var \Drupal\Core\Asset\LibraryDiscoveryInterface
   */
  protected $libraryDiscovery;

  /**
   * The config factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactory
   */
  protected $configFactory;

  /**
   * Constructs a \Drupal\ckeditor\Plugin\CKEditorPlugin\AutoEmbed object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Asset\LibraryDiscoveryInterface $library_discovery
   *   The library discovery service.
   * @param \Drupal\Core\Config\ConfigFactory $config_factory
   *   The config factory service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, LibraryDiscoveryInterface $library_discovery, ConfigFactory $config_factory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->libraryDiscovery = $library_discovery;
    $this->configFactory = $config_factory;
  }

  /**
   * Creates an instance of the plugin.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The container to pull out services used in the plugin.
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   *
   * @return static
   *   Returns an instance of this plugin.
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('library.discovery'),
      $container->get('config.factory')
    );
  }

  /**
   * Compares the specified version against the currently installed version.
   *
   * @param string $version
   *   The version number that should be compared against the CKEditor version.
   *
   * @return int
   *   Returns returns -1 if the first version is lower than the specified
   *   version, 0 if they are equal, and 1 if the specified version is lower.
   *
   * @see version_compare()
   */
  protected function versionCompare($version) {
    $plugins_version = AssetManager::getPluginsVersion($this->libraryDiscovery, $this->configFactory);
    return version_compare($plugins_version, $version);
  }

}
