<?php
namespace Drupal\migrate_boards\Plugin\migrate\process;

use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateException;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;
use Drupal\taxonomy\Entity\Term;

/**
 * This plugin converts a string to uppercase.
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
      $term = Term::create([
        'name' => $value,
        'vid' => 'organizations',
      ]);
      $term->save();
      $tid = $term->id();
    } else {
      // \Drupal::logger('lookup_term')->error('existing tid: ' . $tid);
    }

    // \Drupal::logger('lookup_term')->error('tid: ' . $tid);
    return $tid;
  }
}
