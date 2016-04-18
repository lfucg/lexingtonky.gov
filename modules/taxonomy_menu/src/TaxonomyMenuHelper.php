<?php

/**
 * @file
 * Contains Drupal\taxonomy_menu\Controller\TaxonomyMenu.
 */

namespace Drupal\taxonomy_menu;

use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Menu\MenuLinkManagerInterface;
use Drupal\taxonomy\TermInterface;

/**
 * Class TaxonomyMenu.
 *
 * @package Drupal\taxonomy_menu
 */
class TaxonomyMenuHelper {

  /**
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $menuStorage;

  /**
   * @var \Drupal\Core\Menu\MenuLinkManagerInterface
   */
  protected $manager;

  public function __construct(EntityManagerInterface $entity_manager, MenuLinkManagerInterface $manager) {
    $this->menuStorage = $entity_manager->getStorage('taxonomy_menu');
    $this->manager = $manager;
  }

  /**
   * A reverse lookup of a taxonomy term menus by vocabulary.
   *
   * @return \Drupal\taxonomy_menu\TaxonomyMenuInterface[]
   */
  public function getTermMenusByVocabulary($vid) {
    return $this->menuStorage->loadByProperties(['vocabulary'=>$vid]);
  }

  /**
   * Create menu entries associate with the vocabulary of this term.
   *
   * @param \Drupal\taxonomy\TermInterface $term
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

        $this->manager->updateDefinition($plugin_id, $plugin_def, FALSE);
      }
    }
  }

  /**
   * Remove menu entries associate with the vocabulary of this term.
   *
   * @param \Drupal\taxonomy\TermInterface $term
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
