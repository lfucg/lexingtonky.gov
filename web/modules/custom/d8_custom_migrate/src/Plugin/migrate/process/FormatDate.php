<?php

namespace Drupal\custom_migrate\Plugin\migrate\process;

/**
 * @file
 * Contains \Drupal\migrate\Plugin\migrate\process\FormatDate.
 */

use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;

/**
 * Formats the date of migrated date.
 *
 * @MigrateProcessPlugin(
 *   id = "format_date",
 * )
 */
class FormatDate extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {

    if (!$value) {
      return FALSE;
    }

    $date = new \DateTime($value, new \DateTimeZone('America/New_York'));
    $format = 'Y-m-d\TH:i:s';
    $date->setTimezone(new \DateTimeZone('UTC'));
    $formatted = $date->format($format);

    return $formatted;
  }

}
