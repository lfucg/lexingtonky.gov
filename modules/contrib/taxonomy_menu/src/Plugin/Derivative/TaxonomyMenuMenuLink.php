<?php

namespace Drupal\taxonomy_menu\Plugin\Derivative;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides menu links for Taxonomy Menus.
 *
 * @see \Drupal\taxonomy_menu\Plugin\Menu\TaxonomyMenuMenuLink
 */
class TaxonomyMenuMenuLink extends DeriverBase implements ContainerDeriverInterface {

  /**
   * The taxonomy menu storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $taxonomyMenuStorage;

  /**
   * Sets up the storage handler.
   *
   * @param \Drupal\Core\Entity\EntityStorageInterface $taxonomy_menu_storage
   *   The taxonomy menu storage.
   */
  public function __construct(EntityStorageInterface $taxonomy_menu_storage) {
    $this->taxonomyMenuStorage = $taxonomy_menu_storage;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id) {
    return new static(
      $container->get('entity_type.manager')->getStorage('taxonomy_menu')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    $links = [];
    /* @var $taxonomy_menus \Drupal\taxonomy_menu\TaxonomyMenuInterface[] */
    $taxonomy_menus = $this->taxonomyMenuStorage->loadMultiple();

    // MenuLinkContent entity, menulinkcontent table, look for data.
    foreach ($taxonomy_menus as $taxonomy_menu) {
      /* @var $taxonomy_menu \Drupal\taxonomy_menu\TaxonomyMenuInterface */
      $taxonomy_menu->getMenu();
      $links = array_merge($links, $taxonomy_menu->getLinks($base_plugin_definition));
    }

    return $links;
  }

}
