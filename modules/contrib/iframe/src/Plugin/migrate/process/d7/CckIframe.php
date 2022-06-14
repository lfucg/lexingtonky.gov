<?php

namespace Drupal\iframe\Plugin\migrate\process\d7;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\migrate\Entity\MigrationInterface;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class CckIframe.
 *
 * @MigrateProcessPlugin(
 *   id = "d7_cck_iframe"
 * )
 */
class CckIframe extends ProcessPluginBase implements ContainerFactoryPluginInterface {

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, MigrationInterface $migration) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->migration = $migration;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition, MigrationInterface $migration = NULL) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $migration
    );
  }

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $attributes = unserialize($value['attributes']);
    // Drupal 6 iframe attributes might be double serialized.
    if (!is_array($attributes)) {
      try {
        $attributes = unserialize($attributes);
      }
      catch (Exception $e) {
        // Ignore and set default attributes were
        // Only optional and ar not necessarily required.
        $attributes = [];
      }
    }

    // Massage the values into the correct form for the iframe.
    foreach ($attributes as $akey => $aval) {
      if (isset($akey)) {
        $route[$akey] = (string) $aval;
      }
    }
    $route['url'] = (string) $value['url'];
    $route['title'] = (string) $value['title'];
    $route['width'] = (string) $value['width'];
    $route['height'] = (string) $value['height'];
    $route['tokensupport'] = (int) $value['tokensupport'];
    return $route;
  }
}
