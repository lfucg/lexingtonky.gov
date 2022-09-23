<?php

namespace Drupal\taxonomy_menu\Plugin\Menu;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Menu\MenuLinkBase;
use Drupal\Core\Menu\StaticMenuLinkOverridesInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines menu links provided by taxonomy menu.
 *
 * @see \Drupal\taxonony_menu\Plugin\Derivative\TaxonomyMenuMenuLink
 */
class TaxonomyMenuMenuLink extends MenuLinkBase implements ContainerFactoryPluginInterface {

  /**
   * {@inheritdoc}
   *
   * Other possible overrides:
   * - 'menu_name' => 1,
   * - 'parent' => 1,
   * - 'title' => 1,
   * - 'description' => 1,
   * - 'metadata' => 1,
   */
  protected $overrideAllowed = [
    'weight' => 1,
    'expanded' => 1,
    'enabled' => 1,
    'parent' => 1,
  ];

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The static menu link service used to store updates to weight/parent etc.
   *
   * @var \Drupal\Core\Menu\StaticMenuLinkOverridesInterface
   */
  protected $staticOverride;

  /**
   * Constructs a new TaxonomyMenuMenuLink.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Menu\StaticMenuLinkOverridesInterface $static_override
   *   The static menu override.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    EntityTypeManagerInterface $entity_type_manager,
    StaticMenuLinkOverridesInterface $static_override
  ) {
    $this->configuration = $configuration;
    $this->pluginId = $plugin_id;
    $this->pluginDefinition = $plugin_definition;
    $this->entityTypeManager = $entity_type_manager;
    $this->staticOverride = $static_override;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('menu_link.static.overrides')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getTitle() {
    /** @var \Drupal\taxonomy\Entity\Term. $link */
    $link = $this->entityTypeManager->getStorage('taxonomy_term')
      ->load($this->pluginDefinition['metadata']['taxonomy_term_id']);

    $language = \Drupal::languageManager()->getCurrentLanguage()->getId();
    if (!empty($link) && $link->hasTranslation($language)) {
      $translation = $link->getTranslation($language);
      return $translation->label();
    } else if ($link) {
      return $link->label();
    }

    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    /** @var \Drupal\taxonomy\Entity\Term. $link */
    $link = $this->entityTypeManager->getStorage('taxonomy_term')
      ->load($this->pluginDefinition['metadata']['taxonomy_term_id']);

    // Get the description field name.
    $taxonomy_menu = $this->entityTypeManager->getStorage('taxonomy_menu')->load($this->pluginDefinition['metadata']['taxonomy_menu_id']);
    $description_field_name = !empty($taxonomy_menu) ? $taxonomy_menu->getDescriptionFieldName() : '';

    $language = \Drupal::languageManager()->getCurrentLanguage()->getId();

    if (!empty($link) && $link->hasTranslation($language)) {
      $translation = $link->getTranslation($language);
      if (!empty($translation) && $translation->hasField($description_field_name)) {
        return $translation->{$description_field_name}->value;
      }
    }
    elseif (!empty($link) && $link->hasField($description_field_name)) {
      return $link->{$description_field_name}->value;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function updateLink(array $new_definition_values, $persist) {
    $overrides = array_intersect_key($new_definition_values, $this->overrideAllowed);
    // Update the definition.
    $this->pluginDefinition = $overrides + $this->pluginDefinition;
    if ($persist) {
      // @todo consider any "persistence" back to TaxonomyMenu and/or Taxonomy
      // upon menu link update.
      // Always save the menu name as an override to avoid defaulting to tools.
      $overrides['menu_name'] = $this->pluginDefinition['menu_name'];
      $this->staticOverride->saveOverride($this->getPluginId(), $this->pluginDefinition);
    }
    return $this->pluginDefinition;
  }

  /**
   * {@inheritdoc}
   */
  public function isDeletable() {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function deleteLink() {
  }

  /**
   * {@inheritdoc}
   */
  public function isResettable() {
    $override = $this->staticOverride->loadOverride($this->getPluginId());
    return $override !== NULL && !empty($override);
  }
}
