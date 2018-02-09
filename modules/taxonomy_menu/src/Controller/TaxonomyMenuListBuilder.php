<?php

/**
 * @file
 * Contains Drupal\taxonomy_menu\Controller\TaxonomyMenuListBuilder.
 */

namespace Drupal\taxonomy_menu\Controller;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;

/**
 * Provides a listing of TaxonomyMenu.
 */
class TaxonomyMenuListBuilder extends ConfigEntityListBuilder {
  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['label'] = $this->t('Taxonomy Menu');
    $header['id'] = $this->t('Machine name');
    $header['expanded'] = $this->t('Expanded');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $row['label'] = $entity->label();
    $row['id'] = $entity->id();
    $row['expanded'] = ($entity->expanded) ? $this->t('Yes') : $this->t('No');
    // You probably want a few more properties here...
    return $row + parent::buildRow($entity);
  }

}
