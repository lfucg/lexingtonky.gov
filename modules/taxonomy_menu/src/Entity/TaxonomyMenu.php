<?php

/**
 * @file
 * Contains Drupal\taxonomy_menu\Entity\TaxonomyMenu.
 */

namespace Drupal\taxonomy_menu\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\taxonomy\Entity\Term;
use Drupal\taxonomy\TermInterface;
use Drupal\taxonomy_menu\TaxonomyMenuInterface;

/**
 * Defines the TaxonomyMenu entity.
 *
 * @ConfigEntityType(
 *   id = "taxonomy_menu",
 *   label = @Translation("Taxonomy menu"),
 *   handlers = {
 *     "list_builder" = "Drupal\taxonomy_menu\Controller\TaxonomyMenuListBuilder",
 *     "form" = {
 *       "add" = "Drupal\taxonomy_menu\Form\TaxonomyMenuForm",
 *       "edit" = "Drupal\taxonomy_menu\Form\TaxonomyMenuForm",
 *       "delete" = "Drupal\Core\Entity\EntityDeleteForm"
 *     }
 *   },
 *   config_prefix = "taxonomy_menu",
 *   admin_permission = "administer site configuration",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "edit-form" = "/admin/structure/taxonomy_menu/{taxonomy_menu}",
 *     "delete-form" = "/admin/structure/taxonomy_menu/{taxonomy_menu}/delete",
 *     "collection" = "/admin/structure/taxonomy_menu"
 *   }
 * )
 */
class TaxonomyMenu extends ConfigEntityBase implements TaxonomyMenuInterface {
  /**
   * The TaxonomyMenu ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The TaxonomyMenu label.
   *
   * @var string
   */
  protected $label;

  /**
   * The taxonomy vocabulary.
   *
   * @var string
   *   The machine name of the vocabulary this entity represents.
   */
  protected $vocabulary;

  /**
   * @todo
   */
  protected $depth;

  /**
   * The menu to embed the vocabulary.
   *
   * @var string
   *   The machine name of the menu entity.
   */
  protected $menu;

  /**
   * The expanded mode.
   *
   * @var boolean
   */
  public $expanded;

   /**
   * @todo
   */
  protected $menu_parent;

  /**
   * {@inheritdoc}
   */
  public function getVocabulary() {
    return $this->vocabulary;
  }

  /**
   * {@inheritdoc}
   */
  public function getDepth() {
    return $this->depth;
  }

  /**
   * {@inheritdoc}
   */
  public function getMenu() {
    return $this->menu;
  }

  /**
   * {@inheritdoc}
   */
  public function getMenuParent() {
    return $this->menu_parent;
  }

  /**
   * {@inheritdoc}
   */
  public function preSave(EntityStorageInterface $storage) {
    parent::preSave($storage);
    if (!$this->isNew()) {
      foreach (array_keys($this->getLinks([], TRUE)) as $link_key) {
        $this->getMenuLinkManager()->removeDefinition($link_key, FALSE);
      }
    }
    $this->addDependency('config', 'system.menu.' . $this->getMenu());
    $this->addDependency('config', 'taxonomy.vocabulary.' . $this->getVocabulary());
  }

  /**
   * {@inheritdoc}
   */
  public function save() {
    // Make sure we don't have any save exceptions before building menu
    // definitions.
    $return = parent::save();
    foreach ($this->getLinks([], TRUE) as $link_key => $link_def) {
      $this->getMenuLinkManager()->addDefinition($link_key, $link_def);
    }
    return $return;
  }

  /**
   * {@inheritdoc}
   */
  public function delete() {
    foreach (array_keys($this->getLinks([], TRUE)) as $link_key) {
      $this->getMenuLinkManager()->removeDefinition($link_key, FALSE);
    }
    parent::delete();
  }


  /**
   * {@inheritdoc}
   */
  public function getLinks($base_plugin_definition = [], $include_base_plugin_id = FALSE) {
    /** @var $termStorage \Drupal\taxonomy\TermStorageInterface */
    $termStorage = $this->entityTypeManager()->getStorage('taxonomy_term');
    // Load taxonomy terms for tax menu vocab.
    $terms = $termStorage->loadTree($this->getVocabulary(), 0, $this->getDepth() + 1);

    $links = [];

    // Create menu links for each term in the vocabulary.
    foreach ($terms as $term) {
      if (!$term instanceof TermInterface) {
        $term = Term::load($term->tid);
      }
      $mlid = $this->buildMenuPluginId($term, $include_base_plugin_id);

      $links[$mlid] = $this->buildMenuDefinition($term, $base_plugin_definition);
    }

    return $links;
  }

  /**
   * @return \Drupal\Core\Menu\MenuLinkManagerInterface
   */
  protected function getMenuLinkManager() {
    return \Drupal::service('plugin.manager.menu.link');
  }

  /**
   * {@inheritdoc}
   */
  public function buildMenuPluginId(TermInterface $term, $include_base_plugin_id = TRUE) {
    $plugin_id = '';
    if ($include_base_plugin_id) {
      $plugin_id .= 'taxonomy_menu.menu_link:';
    }
    $plugin_id .= 'taxonomy_menu.menu_link.' . $this->id() . '.' . $term->id();
    return $plugin_id;
  }

  /**
   * Generate a menu link plugin definition for a taxonomy term.
   *
   * @param \Drupal\taxonomy\TermInterface $term
   *  The taxonomy term for which to build a menu link render array.
   * @param $base_plugin_definition
   *  The base plugin definition to merge the link with.
   *
   * @return array
   */
  protected function buildMenuDefinition(TermInterface $term, $base_plugin_definition) {
    $term_id = $term->id();
    $term_url = $term->toUrl();
    $taxonomy_menu_id = $this->id();
    $menu_id = $this->getMenu();

    // Determine parent link.
    // TODO: Evaluate use case of multiple parents (should we make many menu items?)
    $menu_parent_id = NULL;
    /** @var $termStorage \Drupal\taxonomy\TermStorageInterface */
    $termStorage = $this->entityTypeManager()->getStorage('taxonomy_term');
    $parents = $termStorage->loadParents($term_id);
    $parents = array_values($parents);

    if (is_array($parents) && count($parents) && !is_null($parents[0]) && $parents[0] != '0') {
      $menu_parent_id = $this->buildMenuPluginId($parents[0]);
    }

    // Please note: if menu_parent_id is NULL, it will not update the hierarchy properly.
    if (empty($menu_parent_id)) {
      $menu_parent_id = str_replace($this->getMenu() . ':', '', $this->getMenuParent());
    }

    // TODO: Consider implementing a forced weight based on taxonomy tree.

    // Generate link.
    $arguments = ['taxonomy_term' => $term_id];

    $link = $base_plugin_definition;

    $link += array(
      'id' => $this->buildMenuPluginId($term),
      'title' => $term->label(),
      'description' => $term->getDescription(),
      'menu_name' => $menu_id,
      'expanded' => $this->expanded,
      'metadata' => array(
        'taxonomy_menu_id' => $taxonomy_menu_id,
        'taxonomy_term_id' => $term_id,
      ),
      'route_name' => $term_url->getRouteName(),
      'route_parameters' => $term_url->getRouteParameters(),
      'load arguments'  => $arguments,
      'parent' => $menu_parent_id,
      'provider' => 'taxonomy_menu',
      'class' => 'Drupal\taxonomy_menu\Plugin\Menu\TaxonomyMenuMenuLink',
    );

    return $link;
  }
}
