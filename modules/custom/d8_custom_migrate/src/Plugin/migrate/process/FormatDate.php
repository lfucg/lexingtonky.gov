<?php
/**
 * @file
 * Contains \Drupal\migrate\Plugin\migrate\process\FormatDate.
 */

namespace Drupal\custom_migrate\Plugin\migrate\process;

use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateException;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;
use DateTime;
use DateTimeZone;

/**
 * @MigrateProcessPlugin(
 *   id = "format_date",
 * )
 */
class FormatDate extends ProcessPluginBase {
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $date = new DateTime($value, new DateTimeZone('America/New_York'));
    $format = 'Y-m-d\TH:i:s';
    $date->setTimezone(new DateTimeZone('UTC'));
    $formatted = $date->format($format);
    // \Drupal::logger('my_module')->error('value: ' . $value);
    // \Drupal::logger('my_module')->error('formatted: ' . $formatted);
    return $formatted;
  }
}
