<?php

namespace Drupal\Tests\ckeditor_media_embed\Unit {

use Drupal\Tests\UnitTestCase;

use Drupal\ckeditor_media_embed\AssetManager;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Tests the asset manager.
 *
 * @group ckeditor_media_embed
 */
class AssetManagerTest extends UnitTestCase {

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    $container = new ContainerBuilder();
    $container->set('app.root', __DIR__ . '/../../assets');
    \Drupal::setContainer($container);
  }

  /**
   * Tests \Drupal\ckeditor_media_embed\AssetManager::getPlugins().
   */
  public function testGetPlugins() {
    $this->assertTrue(is_array(AssetManager::getPlugins()));
  }

  /**
   * Tests \Drupal\ckeditor_media_embed\AssetManager::getPluginsInstallStatuses().
   */
  public function testGetPluginInstallStatusesMissing() {
    $plugins = AssetManager::getPlugins();
    $plugin_count = count($plugins);
    $statuses = AssetManager::getPluginsInstallStatuses();
    $this->assertCount($plugin_count, $statuses, 'There should be a status for each plugin.');

    $missing_plugins = array_filter($statuses, function ($is_installed) {
      return !$is_installed;
    });
    $this->assertCount($plugin_count - 1, $missing_plugins, 'There should be N - 1 plugins installed.');

    $installed_plugins = array_filter($statuses);
    $this->assertCount(1, $installed_plugins, 'There should only be one installed plugin.');
  }

  /**
   * Tests \Drupal\ckeditor_media_embed\AssetManager::pluginsAreInstalled().
   */
  public function testPluginsAreInstalled() {
    $this->assertFalse(AssetManager::pluginsAreInstalled(), 'All of the plugins should not be installed.');
  }

  /**
   * Tests \Drupal\ckeditor_media_embed\AssetManager::pluginIsInstalled().
   */
  public function testPluginIsInstalled() {
    $this->assertTrue(AssetManager::pluginIsInstalled('embed'), 'The embed plugin should be installed.');
    $this->assertFalse(AssetManager::pluginIsInstalled('embedbase'), 'The embedbase plugin should NOT be installed.');
  }

  /**
   * Tests \Drupal\ckeditor_media_embed\AssetManager::getCKEditorVersion().
   */
  // @codingStandardsIgnoreLine
  public function testGetCKEditorVersion() {
    $this->libraryDiscovery = $this->getMockBuilder('Drupal\Core\Asset\LibraryDiscovery')
      ->disableOriginalConstructor()
      ->setMethods(['getLibraryByName'])
      ->getMock();
    $this->libraryDiscovery->expects($this->once())
      ->method('getLibraryByName')
      ->with('core', 'ckeditor')
      ->will($this->returnValue(['version' => 'x.x.x']));

    $this->assertSame('x.x.x', AssetManager::getCKEditorVersion($this->libraryDiscovery), 'The version should be retrieved from the core CKEditor library.');
  }

  /**
   * Tests \Drupal\ckeditor_media_embed\AssetManager::getCKEditorLibraryPluginPath().
   */
  // @codingStandardsIgnoreLine
  public function testGetCKEditorLibraryPluginPath() {
    $this->assertSame('libraries/ckeditor/plugins/', AssetManager::getCKEditorLibraryPluginPath());
  }

  /**
   * Tests \Drupal\ckeditor_media_embed\AssetManager::getCKEditorLibraryPluginDirectory().
   */
  // @codingStandardsIgnoreLine
  public function testGetCKEditorLibraryPluginDirectory() {
    $this->assertSame(__DIR__ . '/../../assets/libraries/ckeditor/plugins/', AssetManager::getCKEditorLibraryPluginDirectory());
  }

  /**
   * Tests \Drupal\ckeditor_media_embed\AssetManager::getCKEditorDevFullPackageUrl().
   */
  // @codingStandardsIgnoreLine
  public function testGetCKEditorDevFullPackageUrl() {
    $this->assertSame('https://github.com/ckeditor/ckeditor-dev/archive/x.x.x.zip', AssetManager::getCKEditorDevFullPackageUrl('x.x.x'));
  }

  /**
   * Tests \Drupal\ckeditor_media_embed\AssetManager::getCKEditorDevFullPackageName().
   */
  // @codingStandardsIgnoreLine
  public function testGetCKEditorDevFullPackageName() {
    $this->assertSame('ckeditor-dev-x.x.x', AssetManager::getCKEditorDevFullPackageName('x.x.x'));
  }

}
}

// This is needed for the test of AssetManager::getCKEditorLibraryPluginPath().
// @todo Remove as part of https://www.drupal.org/node/2481833.
namespace {
  if (!function_exists('base_path')) {

    /**
     * Returns the base URL path (i.e., directory) of the Drupal installation.
     */
    function base_path() {
      return '/';
    }

  }
}
