<?php

namespace Drupal\search_api\Entity;

@trigger_error('\Drupal\search_api\Entity\TaskStorageSchema is deprecated in search_api:8.x-1.23 and is removed from search_api:2.0.0. There is no replacement. See https://www.drupal.org/node/3247781.', E_USER_DEPRECATED);

use Drupal\Core\Entity\ContentEntityTypeInterface;
use Drupal\Core\Entity\Sql\SqlContentEntityStorageSchema;

/**
 * Defines a storage schema for task entities.
 *
 * @deprecated in search_api:8.x-1.23 and is removed from search_api:2.0.0.
 *   There is no replacement.
 *
 * @see https://www.drupal.org/node/3247781
 */
class TaskStorageSchema extends SqlContentEntityStorageSchema {

  /**
   * {@inheritdoc}
   */
  protected function getEntitySchema(ContentEntityTypeInterface $entity_type, $reset = FALSE): array {
    $schema = parent::getEntitySchema($entity_type, $reset);

    $data_table = $this->storage->getBaseTable();
    if ($data_table) {
      $column = 'data';
      // MySQL cannot handle UNIQUE indices on TEXT/BLOB fields without a prefix
      // length.
      if ($this->database->driver() === 'mysql') {
        // From the MySQL documentation:
        // https://dev.mysql.com/doc/refman/8.0/en/innodb-limits.html
        //
        // The index key prefix length limit is 767 bytes for InnoDB tables that
        // use the REDUNDANT or COMPACT row format. For example, you might hit
        // this limit with a column prefix index of more than 191 characters on
        // a TEXT or VARCHAR column, assuming a utf8mb4 character set and the
        // maximum of 4 bytes for each character.
        //
        // To be on the safe side let's assume utf8mb4 character set.
        $column = ['data', 191];
      }
      $schema[$data_table]['unique keys'] += [
        'task__unique' => ['type', 'server_id', 'index_id', $column],
      ];
    }

    return $schema;
  }

}
