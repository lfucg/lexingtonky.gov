<?php

namespace Drupal\ckeditor_media_embed;

use Drupal\Core\Asset\LibraryDiscoveryInterface;
use Drupal\Core\Serialization\Yaml;
use Drupal\Core\Config\ConfigFactory;

/**
 * The AssetManager facade for managing CKEditor plugins.
 */
class AssetManager {

  private static $libraryVersion = '4.5.x';
  private static $packagePrefix = 'ckeditor4';

  /**
   * Retrieve a list of all plugins to install.
   *
   * @return array
   *   An array of CKEditor plugin names that will be installed.
   */
  public static function getPlugins($version = '') {
    if (empty($version)) {
      $version = self::$libraryVersion;
    }

    $plugins = [
      'autoembed',
      'autolink',
      'embed',
      'embedbase',
      'embedsemantic',
      'notification',
      'notificationaggregator',
    ];

    // Text match was added as a new dependency of autolink as of 4.11.
    if (version_compare($version, '4.11', '>=')) {
      $plugins[] = 'textmatch';
    }

    return $plugins;
  }

  /**
   * Retrieve the install status of all CKEditor plugins.
   */
  public static function getPluginsInstallStatuses($version = '') {
    $plugin_statuses = [];

    foreach (self::getPlugins($version) as $plugin_name) {
      $plugin_statuses[$plugin_name] = self::pluginIsInstalled($plugin_name);
    }

    return $plugin_statuses;
  }

  /**
   * Determine if all our CKEditor plugins are installed.
   *
   * @return bool
   *   Returns TRUE if all of our CKEditor plugins are installed and FALSE
   *   otherwise.
   */
  public static function pluginsAreInstalled($version = '') {
    $installed = TRUE;

    foreach (self::getPlugins($version) as $plugin_name) {
      if (!self::pluginIsInstalled($plugin_name)) {
        $installed = FALSE;
      }
    }

    return $installed;
  }

  /**
   * Determine if the specified plugin is installed.
   *
   * @param string $plugin_name
   *   The name of the plugin to check installation.
   *
   * @return bool
   *   Returns TRUE if the specfied CKEditor plugin is installed and FALSE
   *   otherwise.
   */
  public static function pluginIsInstalled($plugin_name) {
    $is_installed = FALSE;

    $library_plugin_path = self::getCKEditorLibraryPluginDirectory() . $plugin_name;
    if (is_dir($library_plugin_path) && is_file($library_plugin_path . '/plugin.js')) {
      $is_installed = TRUE;
    }

    return $is_installed;
  }

  /**
   * Retrieve version number of the currently installed CKEditor.
   *
   * @param \Drupal\Core\Asset\LibraryDiscoveryInterface $library_discovery
   *   The library discovery service to use for retrieving information about
   *   the CKeditor library.
   * @param \Drupal\Core\Config\ConfigFactory
   *   The config factory service to use for retrieving configuration
   *   information about CKeditor Media Embed.
   * @param string $path
   *   The path to the library yml file, from the root. Defaults to 'core'.
   * @param string $extension
   *   The extension type of the library file. Defaults to 'core'.
   *
   * @return string
   *   The version number of the currently installed CKEditor.
   */
  // @codingStandardsIgnoreLine
  public static function getCKEditorVersion(LibraryDiscoveryInterface $library_discovery, ConfigFactory $config_factory, $path = 'core', $extension = 'core') {
    $config_version = $config_factory->get('ckeditor_media_embed.settings')->get('ckeditor_version');
    if (!empty($config_version)) {
      return $config_version;
    }

    $parsed_version = self::parseForCoreCKEditorVersion($path, $extension);
    return empty($parsed_version) ? self::$libraryVersion : $parsed_version;
  }

  /**
   * Retrieve the currently installed version of the CKEditor plugins.
   *
   * @param \Drupal\Core\Config\ConfigFactory $config_factory
   *   The config factory service to use for retrieving configuration settings.
   *
   * @return string
   *   The version number of the currently installed CKEditor plugins.
   */
  public static function getPluginsInstalledVersion(ConfigFactory $config_factory) {
    return $config_factory->get('ckeditor_media_embed.settings')->get('plugins_version_installed');
  }

  /**
   * Get the plugins version.
   *
   * @param \Drupal\Core\Asset\LibraryDiscoveryInterface $library_discovery
   *   The library discovery service to use for retrieving information about
   *   the CKeditor library.
   * @param \Drupal\Core\Config\ConfigFactory $config_factory
   *   The config factory service to use for retrieving configuration
   *   information about CKeditor Media Embed.
   * @param string $path
   *   The path to the library yml file, from the root. Defaults to 'core'.
   * @param string $extension
   *   The extension type of the library file. Defaults to 'core'.
   *
   * @return string
   *   The version number of the currently installed CKEditor plugins or if not
   *   installed the version of the installed CKEditor.
   */
  public static function getPluginsVersion(LibraryDiscoveryInterface $library_discovery, ConfigFactory $config_factory, $path = 'core', $extension = 'core') {
    $version = self::getPluginsInstalledVersion($config_factory);

    if (empty($version)) {
      $version = self::getCKEditorVersion($library_discovery, $config_factory);
    }

    return $version;
  }

  /**
   * Parse the core libraries to get the current version of CKEditor.
   *
   * @param string $path
   *   The path to the library from the root.
   * @param string $extension
   *   The extension type of the library.
   *
   * @return string
   *   Core's version of CKEditor.
   *
   * @see \Drupal\Core\Asset\LibraryDiscoveryParser::parseLibraryInfo()
   */
  // @codingStandardsIgnoreLine
  protected static function parseForCoreCKEditorVersion($path, $extension){
    $version = '';
    $libraries = [];

    $library_file = \Drupal::root() . '/' . $path . '/' . $extension . '.libraries.yml';
    if (file_exists($library_file)) {
      try {
        $libraries = Yaml::decode(file_get_contents($library_file));

        if (!empty($libraries['ckeditor']['version'])) {
          $version = $libraries['ckeditor']['version'];

          $version_extra_position = strpos($libraries['ckeditor']['version'], '+');
          if ($version_extra_position > 0) {
            $version = substr($version, 0, $version_extra_position);
          }
        }
      }
      catch (InvalidDataTypeException $e) {
        // Rethrow a more helpful exception to provide context.
        throw new InvalidLibraryFileException(sprintf('Invalid library definition in %s: %s', $library_file, $e->getMessage()), 0, $e);
      }
    }

    return $version;
  }

  /**
   * Retrieve the path of the CKEditor plugins for use in a URL.
   *
   * @return string
   *   The path to the CKEditor plugins for use in a URL.
   */
  // @codingStandardsIgnoreLine
  public static function getCKEditorLibraryPluginPath() {
    return 'libraries/ckeditor/plugins/';
  }

  /**
   * Retrieve the system directory of the CKEditor plugins.
   *
   * The plugin directory is used with commands and abolute system path.
   *
   * @return string
   *   The diretory to the CKEditor plugins for use in a with commands and
   *   absolute system paths.
   */
  // @codingStandardsIgnoreLine
  public static function getCKEditorLibraryPluginDirectory() {
    return \Drupal::root() . '/libraries/ckeditor/plugins/';
  }

  /**
   * Retrieve the URL of the source package to download.
   *
   * @param string $version
   *   The version of the CKEditor source package to download.
   *
   * @return string
   *   The absolute URL to the source package downloadable archive.
   */
  // @codingStandardsIgnoreLine
  public static function getCKEditorDevFullPackageUrl($version) {
    return 'https://github.com/ckeditor/' . self::$packagePrefix . '/archive/' . $version . '.zip';
  }

  /**
   * Retrieve the source CKEditor package name based on the package version.
   *
   * @param string $version
   *   The version of the CKEditor source package.
   */
  // @codingStandardsIgnoreLine
  public static function getCKEditorDevFullPackageName($version) {
    return self::$packagePrefix . '-' . $version;
  }

}
