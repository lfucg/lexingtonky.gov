<?php
namespace Drupal\migrate_boards\Plugin\migrate\process;

use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateException;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;
use Drupal\taxonomy\Entity\Term;

/**
 * This plugin looks up a taxonomy term
 *
 * @MigrateProcessPlugin(
 *   id = "lookup_term"
 * )
 */
class LookupTerm extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {

    $tid = array_keys(\Drupal::entityQuery('taxonomy_term')
      ->condition('name', trim($value))
      ->condition('vid', 'organizations')
      ->execute())[0];

    if (! $tid) {
      \Drupal::logger('lookup_term')->error('no tid! ' . $value);
    }
    return $tid;
  }
}
