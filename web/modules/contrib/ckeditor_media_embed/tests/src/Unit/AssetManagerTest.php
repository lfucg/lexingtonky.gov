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
    public function setUp(): void {
      $container = new ContainerBuilder();
      $container->set('app.root', __DIR__ . '/../../assets');
      \Drupal::setContainer($container);
    }

    /**
     * Tests \Drupal\ckeditor_media_embed\AssetManager::getPlugins().
     */
    public function testGetPlugins() {
      $plugins = AssetManager::getPlugins();
      $this->assertIsArray($plugins);
      $this->assertCount(9, $plugins, 'There should be 9 plugins.');
    }

    /**
     * Tests \Drupal\ckeditor_media_embed\AssetManager::getPlugins(<version>).
     */
    public function testGetPluginsAbove411() {
      $plugins = AssetManager::getPlugins('4.11');
      $this->assertCount(10, $plugins, 'There should be 10 plugins.');
    }

    /**
     * Tests \Drupal\ckeditor_media_embed\AssetManager::getPluginsInstallStatuses(<version>).
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
     * Tests \Drupal\ckeditor_media_embed\AssetManager::getPluginsInstallStatuses(<version>).
     */
    public function testGetPluginInstallStatusesAbove411() {
      $plugins = AssetManager::getPlugins('4.11');
      $plugin_count = count($plugins);
      $statuses = AssetManager::getPluginsInstallStatuses('4.11');
      $this->assertCount($plugin_count, $statuses, 'There should be a status for each plugin.');
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
      $library_discovery = $this->getMockBuilder('Drupal\Core\Asset\LibraryDiscovery')
        ->disableOriginalConstructor()
        ->setMethods(['getLibraryByName'])
        ->getMock();

      $config_empty = $this->getMockBuilder('\Drupal\Core\Config\ImmutableConfig')
        ->disableOriginalConstructor()
        ->getMock();
      $config_empty->expects($this->exactly(2))
        ->method('get')
        ->with('ckeditor_version')
        ->willReturn('');

      $config_factory = $this->getMockBuilder('\Drupal\Core\Config\ConfigFactory')
        ->disableOriginalConstructor()
        ->getMock();
      $config_factory->expects($this->exactly(2))
        ->method('get')
        ->with('ckeditor_media_embed.settings')
        ->willReturn($config_empty);

      $container = new ContainerBuilder();
      $container->set('app.root', __DIR__ . '/../../assets');
      $container->set('config.factory', $config_factory);
      \Drupal::setContainer($container);

      $test_library_path = '';
      $test_extension = 'test.core';
      $test_empty_extension = 'empty.test.core';

      // Test with a config file that has no ckeditor_version set, and the
      // drupal library yml file isn't found or can't be parsed.
      $this->assertSame('4.5.x', AssetManager::getCKEditorVersion($library_discovery, $config_factory, $test_library_path, $test_empty_extension), 'The version that should be retrieved is 4.5.x');

      // Test with a config file that has no ckeditor_version set, and the
      // drupal library yml file is able to be found and parsed.
      $this->assertSame('x.x.x', AssetManager::getCKEditorVersion($library_discovery, $config_factory, $test_library_path, $test_extension), 'The version that should be retrieved is x.x.x');

      // Test with a config file that has ckeditor_version set.
      $config_set = $this->getMockBuilder('\Drupal\Core\Config\ImmutableConfig')
        ->disableOriginalConstructor()
        ->getMock();
      $config_set->expects($this->once())
        ->method('get')
        ->with('ckeditor_version')
        ->willReturn('4.5.0');

      $config_factory = $this->getMockBuilder('\Drupal\Core\Config\ConfigFactory')
        ->disableOriginalConstructor()
        ->getMock();
      $config_factory->expects($this->once())
        ->method('get')
        ->with('ckeditor_media_embed.settings')
        ->willReturn($config_set);

      $this->assertSame('4.5.0', AssetManager::getCKEditorVersion($library_discovery, $config_factory, $test_library_path, $test_extension), 'The version that should be retrieved is 4.5.0');
    }

    /**
     * Tests \Drupal\ckeditor_media_embed\AssetManager::getPluginsInstalledVersion()(<version>).
     */
    public function testGetPluginsInstalledVersion() {
      $config_set = $this->getMockBuilder('\Drupal\Core\Config\ImmutableConfig')
        ->disableOriginalConstructor()
        ->getMock();
      $config_set->expects($this->once())
        ->method('get')
        ->with('plugins_version_installed')
        ->willReturn('4.5.0');

      $config_factory = $this->getMockBuilder('\Drupal\Core\Config\ConfigFactory')
        ->disableOriginalConstructor()
        ->getMock();
      $config_factory->expects($this->once())
        ->method('get')
        ->with('ckeditor_media_embed.settings')
        ->willReturn($config_set);

      $this->assertSame('4.5.0', AssetManager::getPluginsInstalledVersion($config_factory), 'The plugin version that should be retrieved is 4.5.0');

    }

    /**
     * Tests \Drupal\ckeditor_media_embed\AssetManager::getPluginsVersion().
     */
    public function testGetPluginsVersion() {
      $test_library_path = '';
      $test_extension = 'test.core';

      // Retrieve the version from the CKEditor plugins.
      $library_discovery = $this->getMockBuilder('Drupal\Core\Asset\LibraryDiscovery')
        ->disableOriginalConstructor()
        ->setMethods(['getLibraryByName'])
        ->getMock();
      $config_set = $this->getMockBuilder('\Drupal\Core\Config\ImmutableConfig')
        ->disableOriginalConstructor()
        ->getMock();
      $config_set->expects($this->once())
        ->method('get')
        ->with('plugins_version_installed')
        ->willReturn('x.x.x');

      $config_factory = $this->getMockBuilder('\Drupal\Core\Config\ConfigFactory')
        ->disableOriginalConstructor()
        ->getMock();
      $config_factory->expects($this->once())
        ->method('get')
        ->with('ckeditor_media_embed.settings')
        ->willReturn($config_set);

      $this->assertSame('x.x.x', AssetManager::getPluginsVersion($library_discovery, $config_factory, $test_library_path, $test_extension), 'The plugin version that should be retrieved is x.x.x');

      // Retrieve the version from the CKEditor.
      $value_map = [
      ['plugins_version_installed', ''],
      ['ckeditor_version', '4.5.0'],
      ];

      $config_set = $this->getMockBuilder('\Drupal\Core\Config\ImmutableConfig')
        ->disableOriginalConstructor()
        ->getMock();
      $config_set->expects($this->exactly(2))
        ->method('get')
        ->will($this->returnValueMap($value_map));

      $config_factory = $this->getMockBuilder('\Drupal\Core\Config\ConfigFactory')
        ->disableOriginalConstructor()
        ->getMock();
      $config_factory->expects($this->exactly(2))
        ->method('get')
        ->with('ckeditor_media_embed.settings')
        ->willReturn($config_set);

      $this->assertSame('4.5.0', AssetManager::getPluginsVersion($library_discovery, $config_factory, $test_library_path, $test_extension), 'The plugin version that should be retrieved is 4.5.0');
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
      $this->assertSame('https://github.com/ckeditor/ckeditor4/archive/x.x.x.zip', AssetManager::getCKEditorDevFullPackageUrl('x.x.x'));
    }

    /**
     * Tests \Drupal\ckeditor_media_embed\AssetManager::getCKEditorDevFullPackageName().
     */
    // @codingStandardsIgnoreLine
    public function testGetCKEditorDevFullPackageName() {
      $this->assertSame('ckeditor4-x.x.x', AssetManager::getCKEditorDevFullPackageName('x.x.x'));
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
