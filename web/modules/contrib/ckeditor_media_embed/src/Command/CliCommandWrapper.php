<?php

namespace Drupal\ckeditor_media_embed\Command;

use Drupal\ckeditor_media_embed\AssetManager;
use Drupal\ckeditor\CKEditorPluginManager;
use Drupal\Core\Archiver\Zip;
use Drupal\Core\Asset\LibraryDiscovery;
use Drupal\Core\Config\ConfigFactory;
use GuzzleHttp\Client;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class CLICommands.
 */
class CliCommandWrapper  {

  /**
   * The CKEditor plugin manager service.
   *
   * @var \Drupal\ckeditor\CKEditorPluginManager
   */
  protected $ckeditorPluginManager;

  /**
   * The http client.
   *
   * @var \GuzzleHttp\Client
   */
  protected $httpClient;

  /**
   * The library discovery service.
   *
   * @var \Drupal\Core\Asset\LibraryDiscovery
   */
  protected $libraryDiscovery;

  /**
   * The config factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactory
   */
  protected $configFactory;

  /**
   * The file system component.
   *
   * @var \Symfony\Component\Filesystem\Filesystem
   */
  protected $fileSystem;

  /**
   * The version of the package to be installed.
   *
   * @var string
   */
  protected $packageVersion;

  /**
   * Constructs CLI commands object.
   *
   * @param \Drupal\ckeditor\CKEditorPluginManager $ckeditorPluginManager
   *   The CKEditor plugin manager service.
   * @param \Drupal\Core\Asset\LibraryDiscovery $libraryDiscovery
   *   The library discover service.
   * @param \GuzzleHttp\Client $httpClient
   *   The http client.
   * @param \Drupal\Core\Config\ConfigFactory $configFactory
   *   The config factory service.
   */
  public function __construct(CKEditorPluginManager $ckeditorPluginManager, LibraryDiscovery $libraryDiscovery, Client $httpClient, ConfigFactory $configFactory) {
    $this->ckeditorPluginManager = $ckeditorPluginManager;
    $this->libraryDiscovery = $libraryDiscovery;
    $this->httpClient = $httpClient;
    $this->configFactory = $configFactory;

    $this->fileSystem = new FileSystem();
    $this->setPackageVersion();
  }

  /**
   * Set the current CKEditor package version that is installed with Drupal.
   *
   * @return $this
   */
  protected function setPackageVersion() {
    $this->packageVersion = AssetManager::getCKEditorVersion($this->libraryDiscovery, $this->configFactory);
    return $this;
  }

  /**
   * Present question to user about overwriting the plugin files.
   *
   * @param $command
   *   A command object (e.g., a Drush or Drupal Console command).
   *
   * @return bool
   *   TRUE if given affirmative response to overwrite plugin files,
   *   FALSE otherwise.
   */
  public function askToOverwritePluginFiles(CKEditorCliCommandInterface $command) {
    $yes = ($command->getInput()->hasOption('yes'))
      ? $command->getInput()->getOption('yes')
      : FALSE;

    if (!$yes) {
      $libraries_path = AssetManager::getCKEditorLibraryPluginDirectory();
      if (file_exists($libraries_path)) {
        $question = sprintf($command->getMessage('question-overwrite-files'), $libraries_path);
        return $command->confirmation($question);
      }
    }

    return TRUE;
  }

  /**
   * Overwrite the plugin files.
   *
   * @param $command
   *   A command object (e.g., a Drush or Drupal Console command).
   * @param $overwrite
   *   User's response regarding overwriting plugin files.
   */
  public function overwritePluginFiles(CKEditorCliCommandInterface $command, $overwrite) {
    $package_directory = $this->downloadCKEditorFull($command);

    foreach (AssetManager::getPlugins($this->packageVersion) as $plugin) {
      $this->installCKEditorPlugin($command, $package_directory, $plugin, $overwrite);
    }

    $this->configFactory->getEditable('ckeditor_media_embed.settings')->set('plugins_version_installed', $this->packageVersion)->save();
    $this->ckeditorPluginManager->clearCachedDefinitions();
  }

  /**
   * Download the full source package of CKEditor and extract it.
   *
   * @param $command
   *   A command object (e.g., a Drush or Drupal Console command).
   *
   * @return string
   *   The path to the downloaded and extracted package.
   */
  // @codingStandardsIgnoreLine
  protected function downloadCKEditorFull(CKEditorCliCommandInterface $command) {
    $command->comment(sprintf(
      $command->getMessage('comment-downloading-package'), $this->packageVersion
    ));

    $package_name = AssetManager::getCKEditorDevFullPackageName($this->packageVersion);
    $package_url = AssetManager::getCKEditorDevFullPackageUrl($this->packageVersion);
    $package_directory = sys_get_temp_dir() . '/' . $package_name;
    $package_archive = sys_get_temp_dir() . "/$package_name.zip";

    try {
      $this->downloadFile($package_url, $package_archive);
      if (is_file($package_archive)) {
        $archive = new Zip($package_archive);
        $archive->extract(sys_get_temp_dir());
        $command->getIo()->success(sprintf(
          $command->getMessage('success-downloading-package'), $this->packageVersion
        ));
      }
    }
    catch (\Exception $e) {
      $command->getIo()->error($e->getMessage());
    }

    return $package_directory;
  }

  /**
   * Install an individual CKEditor plugin.
   *
   * @param $command
   *   A command object (e.g., a Drush or Drupal Console command).
   * @param string $package_directory
   *   The full path to the downloaded CKEditor full development package.
   * @param string $plugin_name
   *   The machine name of the CKEditor plugin to install.
   * @param $overwrite
   *   User's response regarding overwriting plugin files.
   *
   * @return $this
   */
  // @codingStandardsIgnoreLine
  protected function installCKEditorPlugin(CKEditorCliCommandInterface $command, $package_directory, $plugin_name, $overwrite = FALSE) {
    $libraries_path = AssetManager::getCKEditorLibraryPluginDirectory() . $plugin_name;
    $package_plugin_path = $package_directory . '/plugins/' . $plugin_name;

    try {
      $this->fileSystem->mkdir($libraries_path);
      $this->fileSystem->mirror($package_plugin_path, $libraries_path, NULL, ['override' => $overwrite]);

      $command->getIo()->success(sprintf($command->getMessage('success-installed-plugin'), $plugin_name));
    }
    catch (IOExceptionInterface $e) {
      $command->getIo()->error($e->getMessage());
    }

    return $this;
  }

  /**
   * Download a file.
   *
   * @param string $url
   *   The full URL to the file to download.
   * @param string $destination
   *   The location to place the file.
   *
   * @return bool
   *   Returns TRUE if the file was downloaded as expected, otherwise FALSE.
   */
  protected function downloadFile($url, $destination) {
    $this->httpClient->get($url, ['sink' => $destination]);
    return file_exists($destination);
  }

}
