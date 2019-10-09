<?php

namespace Drupal\ckeditor_media_embed;

use Drupal\Core\Asset\LibraryDiscoveryInterface;

/**
 * The AssetManager facade for managing CKEditor plugins.
 */
class AssetManager {

  private static $libraryVersion = '4.5.x';
  private static $packagePrefix = 'ckeditor-dev';

  /**
   * Retrieve a list of all plugins to install.
   *
   * @return array
   *    An array of CKEditor plugin names that will be installed.
   */
  public static function getPlugins() {
    return [
      'autoembed',
      'autolink',
      'embed',
      'embedbase',
      'embedsemantic',
      'notification',
      'notificationaggregator',
    ];
  }

  /**
   * Retrieve the install status of all CKEditor plugins.
   */
  public static function getPluginsInstallStatuses() {
    $plugin_statuses = array();

    foreach (self::getPlugins() as $plugin_name) {
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
  public static function pluginsAreInstalled() {
    $installed = TRUE;

    foreach (self::getPlugins() as $plugin_name) {
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
   * @param LibraryDiscoveryInterface $library_discovery
   *   The library discovery service to use for retrieving information about
   *   the CKeditor library.
   *
   * @return string
   *   The version number of the currently installed CKEditor.
   */
  // @codingStandardsIgnoreLine
  public static function getCKEditorVersion(LibraryDiscoveryInterface $library_discovery) {
    $version = self::$libraryVersion;

    $ckeditor_library = $library_discovery->getLibraryByName('core', 'ckeditor');

    if (!empty($ckeditor_library['version'])) {
      $version = $ckeditor_library['version'];
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
