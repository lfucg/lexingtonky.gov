<?php

namespace Drupal\plugin_type_example\Plugin\Sandwich;

use Drupal\Core\Plugin\PluginBase;
use Drupal\plugin_type_example\SandwichInterface;

/**
 * Provides a ham sandwich.
 *
 * Because the plugin manager class for our plugins uses annotated class
 * discovery, our ham sandwich only needs to exist within the Plugin\Sandwich
 * namespace to be declared as a plugin. This is defined in
 * \Drupal\plugin_type_example\SandwichPluginManager::__construct().
 *
 * The following is the plugin annotation. This is parsed by Doctrine to make
 * the plugin definition. Any values defined here will be available in the
 * plugin definition.
 *
 * This should be used for metadata that is specifically required to instantiate
 * the plugin, or for example data that might be needed to display a list of all
 * available plugins where the user selects one. This means many plugin
 * annotations can be reduced to a plugin ID, a label and perhaps a description.
 *
 * @Plugin(
 *   id = "ham_sandwich",
 *   foobar = @Translation("This is an example value that is defined in the annotation."),
 *   calories = 426,
 * )
 */
class ExampleHamSandwich extends PluginBase implements SandwichInterface {

  /**
   * Get a description of the sandwich fillings.
   *
   * This is just an example method on our plugin that we can call to get
   * something back.
   */
  public function description() {
    return $this->t('Ham, mustard, rocket, sun-dried tomatoes.');
  }

}
