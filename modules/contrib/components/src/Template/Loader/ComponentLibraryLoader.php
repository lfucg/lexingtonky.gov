<?php

/**
 * @file
 * Contains \Drupal\components\Template\Loader\ComponentLibraryLoader.
 */

namespace Drupal\components\Template\Loader;

use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Extension\ThemeHandlerInterface;

/**
 * Loads templates from the filesystem.
 *
 * This loader adds module and theme components paths as namespaces to the Twig
 * filesystem loader so that templates can be referenced by namespace, like
 * @mycomponents/box.html.twig or @mythemeComponents/page.html.twig.
 */
class ComponentLibraryLoader extends \Twig_Loader_Filesystem {

  // Keep track of libraries that we attempt to register.
  protected $libraries = array();

  /**
   * Constructs a new ComponentsLoader object.
   *
   * @param string|array $paths
   *   A path or an array of paths to check for templates.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler service.
   * @param \Drupal\Core\Extension\ThemeHandlerInterface $theme_handler
   *   The theme handler service.
   */
  public function __construct($paths = array(), ModuleHandlerInterface $module_handler, ThemeHandlerInterface $theme_handler) {
    // Don't pass $paths to __contruct() or it will create the default Twig
    // namespace in this Twig loader.
    parent::__construct();

    // Retrieve config settings for module to determine prefixed namespaces.
    // See Drupal\components\Form\ComponentsSettingsForm.php
    $config = \Drupal::config('components.settings');

    // The Drupal\Core\Template\Loader\FilesystemLoader makes a Twig namespace
    // for each module and theme, so we re-create that list here.
    $existing_namespaces = array();

    // Look at each module and theme.
    $extension_types = array(
      'module' => array(
        'handler' => $module_handler,
        'method' => 'getModuleList',
      ),
      'theme' => array(
        'handler' => $theme_handler,
        'method' => 'listInfo',
      ),
    );
    foreach ($extension_types as $type => $extension_type) {
      foreach ($extension_type['handler']->{$extension_type['method']}() as $name => $extension) {
        $existing_namespaces[] = $name;

        // If type is 'module' we need to get the info.
        if ($type == 'module') {
          $info = system_get_info($type, $name);
        }
        else {
          $info = $extension->info;
        }

        // For each library listed in the .info file's component-libraries
        // section, determine the namespace and the path.
        if (isset($info['component-libraries'])) {
          foreach ($info['component-libraries'] as $namespace => $library) {
            $paths = isset($library['paths']) ? $library['paths'] : array();

            // Allow paths to be an array or a string.
            if (!is_array($paths)) {
              $paths = array($paths);
            }

            // Add the extension's path to the library paths.
            foreach ($paths as $key => $path) {
              $paths[$key] = $extension->getPath() . '/' . $path;
            }

            // Prefix the namespace with original name.
            // Prevents overriding components with duplicate valid namespaces.
            if (!empty($config->get('namespace_prefix'))) {
              $namespace = "{$name}_{$namespace}";
            }

            $this->libraries[] = array(
              'type' => $type,
              'name' => $name,
              'namespace' => $namespace,
              'paths' => $paths,
              'error' => FALSE
            );
          }
        }
      }
    }

    // Register the library paths.
    foreach ($this->libraries as &$library) {
      if (isset($library['paths'])) {
        foreach ($library['paths'] as $path) {
          $this->addPath($path, $library['namespace']);
        }
      }
    }
  }
}
