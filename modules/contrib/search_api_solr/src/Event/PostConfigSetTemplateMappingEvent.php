<?php

namespace Drupal\search_api_solr\Event;

use Drupal\Component\EventDispatcher\Event;

final class PostConfigSetTemplateMappingEvent extends Event {

  /**
   * The config-set template mapping.
   *
   * @var array
   */
  protected $configSetTemplateMapping;

  /**
   * Constructs a new class instance.
   *
   * @param array $configset_template_mapping
   *   Reference to config-set template mapping array.
   */
  public function __construct(array &$configset_template_mapping) {
    $this->configSetTemplateMapping = &$configset_template_mapping;
  }

  /**
   * Retrieves the config-set template mapping.
   *
   * @return array
   *   The config-set template mapping array.
   */
  public function getConfigSetTemplateMapping(): array {
    return $this->configSetTemplateMapping;
  }

  /**
   * Set the config-set template mapping.
   *
   * @param array $configset_template_mapping
   */
  public function setConfigSetTemplateMapping(array $configset_template_mapping) {
    $this->configSetTemplateMapping = $configset_template_mapping;
  }
}
