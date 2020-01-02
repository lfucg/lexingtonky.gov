<?php

namespace Drupal\taxonomy_menu;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Menu\MenuLinkManagerInterface;
use Drupal\taxonomy\TermInterface;

/**
 * Class TaxonomyMenu.
 *
 * @package Drupal\taxonomy_menu
 */
class TaxonomyMenuHelper {

  /**
   * Taxonomy Menu storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $menuStorage;

  /**
   * Menu Link Manager.
   *
   * @var \Drupal\Core\Menu\MenuLinkManagerInterface
   */
  protected $manager;

  /**
   * Constructor.
   *
   * @param EntityTypeManagerInterface $entity_type_manager
   * @param \Drupal\Core\Menu\MenuLinkManagerInterface $manager
   *   The menu link manager.
   * @internal param EntityTypeManagerInterface $entity_manager The storage interface.*   The storage interface.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, MenuLinkManagerInterface $manager) {
    $this->menuStorage = $entity_type_manager->getStorage('taxonomy_menu');
    $this->manager = $manager;
  }

  /**
   * A reverse lookup of a taxonomy term menus by vocabulary.
   *
   * @param string $vid
   *   The vocabulary id.
   *
   * @return \Drupal\taxonomy_menu\TaxonomyMenuInterface[]
   *   The Taxonomy Menu
   */
  public function getTermMenusByVocabulary($vid) {
    return $this->menuStorage->loadByProperties(['vocabulary'=>$vid]);
  }

  /**
   * Create menu entries associate with the vocabulary of this term.
   *
   * @param \Drupal\taxonomy\TermInterface $term
   *   Term
   */
  public function generateTaxonomyMenuEntries(TermInterface $term, $rebuild_all = TRUE) {
    // Load relevant taxonomy menus.
    $tax_menus = $this->getTermMenusByVocabulary($term->getVocabularyId());
    foreach ($tax_menus as $menu) {
      foreach ($menu->getLinks([], TRUE) as $plugin_id => $plugin_def) {
        if (!$rebuild_all) {
          $plugin_id_parts = explode('.', $plugin_id);
          $term_id = array_pop($plugin_id_parts);
          if ($term->id() != $term_id) {
            continue;
          }
        }
        if ($this->manager->hasDefinition($plugin_id)) {

          $this->manager->updateDefinition($plugin_id, $plugin_def);
        }
        else {
          $this->manager->addDefinition($plugin_id, $plugin_def);
        }
      }
    }
  }

  /**
   * Update menu entries associate with the vocabulary of this term.
   *
   * @param \Drupal\taxonomy\TermInterface $term
   *   Term
   */
  public function updateTaxonomyMenuEntries(TermInterface $term, $rebuild_all = TRUE) {

    // Load relevant taxonomy menus.
    $tax_menus = $this->getTermMenusByVocabulary($term->getVocabularyId());
    /** @var $menu \Drupal\taxonomy_menu\TaxonomyMenuInterface */
    foreach ($tax_menus as $menu) {

      $links = $menu->getLinks([], TRUE);

      foreach ($links as $plugin_id => $plugin_def) {
        if (!$rebuild_all) {
          $plugin_id_explode = explode('.', $plugin_id);
          $term_id = array_pop($plugin_id_explode);
          if ($term->id() != $term_id) {
            continue;
          }
        }

        if ($this->manager->hasDefinition($plugin_id)) {
          $this->manager->updateDefinition($plugin_id, $plugin_def, FALSE);
        }
        else {
          // Remove specific menu link if vid term is different to this old vid.
          if ($term->original->getVocabularyId() != $term->getVocabularyId()) {
            $this->removeTaxonomyMenuEntries($term->original);
          }
          $this->manager->addDefinition($plugin_id, $plugin_def);
        }
      }
    }
  }

  /**
   * Remove menu entries associate with the vocabulary of this term.
   *
   * @param \Drupal\taxonomy\TermInterface $term
   *   Term.
   * @param bool $rebuild_all
   *   Whether to rebuild all links or not.
   */
  public function removeTaxonomyMenuEntries(TermInterface $term, $rebuild_all = TRUE) {
    // Load relevant taxonomy menus.
    $tax_menus = $this->getTermMenusByVocabulary($term->getVocabularyId());
    /** @var $menu \Drupal\taxonomy_menu\TaxonomyMenuInterface */
    foreach ($tax_menus as $menu) {
      // Remove all links.
      if ($rebuild_all) {
        $links = array_keys($menu->getLinks([], TRUE));
        foreach ($links as $plugin_id) {
          $this->manager->removeDefinition($plugin_id, FALSE);
        }
      // Remove specific term link. Note - this link does not exist in the taxonomy menu and is not in $links.
      } else if (!empty($term)) {
        $this->manager->removeDefinition($menu->buildMenuPluginId($term), FALSE);
      }
    }
  }

}
