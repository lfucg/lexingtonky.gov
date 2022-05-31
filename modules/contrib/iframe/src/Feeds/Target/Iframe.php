<?php

namespace Drupal\iframe\Feeds\Target;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\feeds\FieldTargetDefinition;
use Drupal\feeds\Plugin\Type\Target\FieldTargetBase;

/**
 * Defines a iframe field mapper.
 *
 * @FeedsTarget(
 *   id = "iframe_feeds_target",
 *   field_types = {"iframe"}
 * )
 */
class Iframe extends FieldTargetBase {

  /**
   * {@inheritdoc}
   */
  protected static function prepareTarget(FieldDefinitionInterface $field_definition) {
    $definition = FieldTargetDefinition::createFromFieldDefinition($field_definition);
    if ($field_definition->getType() === 'iframe') {
      $definition
        ->addProperty('title')
        ->addProperty('url')
        ->addProperty('width')
        ->addProperty('height');
    }
    return $definition;
  }
  
  /**
   * {@inheritdoc}
   */
  protected function prepareValue($delta, array &$values) {
    $values['uri'] = trim($values['uri']);

    // Support linking to nothing.
    if (in_array($values['uri'], ['<nolink>', '<none>'], TRUE)) {
      $values['uri'] = 'route:' . $values['uri'];
    }
    // Detect a schemeless string, map to 'internal:' URI.
    elseif (!empty($values['uri']) && parse_url($values['uri'], PHP_URL_SCHEME) === NULL) {
      // @todo '<front>' is valid input for BC reasons, may be removed by
      //   https://www.drupal.org/node/2421941
      // - '<front>' -> '/'
      // - '<front>#foo' -> '/#foo'
      if (strpos($values['uri'], '<front>') === 0) {
        $values['uri'] = '/' . substr($values['uri'], strlen('<front>'));
      }
      // Prepend only with 'internal:' if the uri starts with '/', '?' or '#'.
      if (in_array($values['uri'][0], ['/', '?', '#'], TRUE)) {
        $values['uri'] = 'internal:' . $values['uri'];
      }
    }
  }

}
