<?php

namespace Drupal\search_api\Plugin\views\field;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\TypedData\ComplexDataInterface;
use Drupal\views\Plugin\views\field\EntityOperations;
use Drupal\views\ResultRow;

/**
 * Renders all operations links for an entity.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("search_api_entity_operations")
 */
class SearchApiEntityOperations extends EntityOperations {

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {
    $build = [];

    $entity = $this->getContainedEntity($values->_item->getOriginalObject());
    if ($entity) {
      $entity_type = $entity->getEntityType();
      if ($entity_type->hasListBuilderClass()) {
        $operations = $this->entityManager->getListBuilder($entity_type->id())
          ->getOperations($entity);
        if ($this->options['destination']) {
          foreach ($operations as $i => $operation) {
            if (!isset($operation['query'])) {
              $operation['query'] = [];
            }
            $operation['query'] += $this->getDestinationArray();
            $operations[$i] = $operation;
          }
        }
        $build = [
          '#type' => 'operations',
          '#links' => $operations,
          '#cache' => [
            'contexts' => ['url'],
          ],
        ];
      }
    }

    return $build;

  }

  /**
   * Retrieves the entity from a search item.
   *
   * @param \Drupal\Core\TypedData\ComplexDataInterface $item
   *   An item of this datasource's type.
   *
   * @return \Drupal\Core\Entity\EntityInterface|null
   *   The entity object represented by that item, or NULL if none could be
   *   found.
   */
  protected function getContainedEntity(ComplexDataInterface $item) {
    $value = $item->getValue();
    return $value instanceof EntityInterface ? $value : NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function query() {}

}
