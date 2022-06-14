<?php

namespace Drupal\iframe\Plugin\migrate\field;

use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\migrate_drupal\Plugin\migrate\field\FieldPluginBase;
use Drupal\migrate_drupal\Annotation\MigrateField;

/**
 * Field migration plugin from D7 to D8.
 * Class IframeField.
 *
 * @MigrateCckField(
 *   id = "iframe",
 *   core = {7},
 *   type_map = {
 *    "iframe" = "iframe"
 *   },
 *   source_module = "iframe",
 *   destination_module = "iframe"
 * )
 */
class IframeField extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function getFieldFormatterMap() {
    // See d6_field_formatter_settings.yml and CckFieldPluginBase
    // processFieldFormatter().
    return [
      'iframe_formatter_default' => 'iframe_default',
      'iframe_formatter_iframeonly' => 'iframe_only',
      'iframe_formatter_asurl' => 'iframe_asurl',
      'iframe_formatter_asurl_withuri' => 'iframe_asurlwithuri',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function processCckFieldValues(MigrationInterface $migration, $field_name, $data) {
    $process = [
      'plugin' => 'd7_cck_iframe',
      'source' => $field_name,
    ];
    $migration->mergeProcessOfProperty($field_name, $process);
  }
}
