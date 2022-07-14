<?php

namespace Drupal\search_api_solr\Event;

use Drupal\Component\EventDispatcher\Event;
use Drupal\search_api\IndexInterface;

final class PostFieldMappingEvent extends Event {

  /**
   * @var IndexInterface
   */
  protected $index;

  /**
   * The field mapping.
   *
   * @var array
   */
  protected $fieldMapping;

  /**
   * @var string
   */
  protected $langcode;

  /**
   * Constructs a new class instance.
   *
   * @param \Drupal\search_api\IndexInterface $index
   * @param array $field_mapping
   *   Reference to field mapping array.
   * @param string $langcode
   */
  public function __construct(IndexInterface $index, array &$field_mapping, string $langcode) {
    $this->index = $index;
    $this->fieldMapping = &$field_mapping;
    $this->langcode = $langcode;
  }

  /**
   * Retrieves the field mapping.
   *
   * @return array
   *   The field mapping array.
   */
  public function getFieldMapping(): array {
    return $this->fieldMapping;
  }

  /**
   * Set the field mapping.
   *
   * @param array $field_mapping
   */
  public function setFieldMapping(array $field_mapping) {
    $this->fieldMapping = $field_mapping;
  }

  /**
   * Retrieves the index.
   */
  public function getIndex(): IndexInterface {
    return $this->index;
  }

  /**
   * Retrieves the language ID.
   *
   * @return string
   *   The language ID.
   */
  public function getLangcode(): string {
    return $this->langcode;
  }
}
