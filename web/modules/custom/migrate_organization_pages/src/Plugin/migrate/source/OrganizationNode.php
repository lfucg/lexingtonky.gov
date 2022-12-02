<?php

namespace Drupal\migrate_organization_pages\Plugin\migrate\source;

/**
 * @file
 * Contains \Drupal\migrate_organization_pages\Plugin\migrate\source\OrganizationNode.
 */

use Drupal\migrate\Plugin\migrate\source\SqlBase;

/**
 * Source plugin for beer content.
 *
 * @MigrateSource(
 *   id = "organization_node"
 * )
 */
class OrganizationNode extends SqlBase {

  /**
   * {@inheritdoc}
   */
  public function query() {
    /*
     * An important point to note is that your query *must* return a single row
     * for each item to be imported. Here we might be tempted to add a join to
     * migrate_example_beer_topic_node in our query, to pull in the
     * relationships to our categories. Doing this would cause the query to
     * return multiple rows for a given node, once per related value, thus
     * processing the same node multiple times, each time with only one of the
     * multiple values that should be imported. To avoid that, we simply query
     * the base node data here, and pull in the relationships in prepareRow()
     * below.
     */
    $query = $this->select('taxonomy_term_field_data', 'b')
      ->fields('b', ['tid', 'name'])
      ->condition('vid', 'organizations');

    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    $fields = [
      'tid' => $this->t('Term ID'),
      'name' => $this->t('Name of term'),
    ];

    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    return [
      'tid' => [
        'type' => 'integer',
        'alias' => 'b',
      ],
    ];
  }

}
