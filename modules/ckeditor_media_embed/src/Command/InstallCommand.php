<?php

namespace Drupal\ckeditor_media_embed\Command;

use Alchemy\Zippy\Zippy;
use Drupal\ckeditor_media_embed\AssetManager;
use Drupal\ckeditor\CKEditorPluginManager;
use Drupal\Console\Annotations\DrupalCommand;
use Drupal\Console\Core\Command\Shared\ContainerAwareCommandTrait;
use Drupal\Console\Core\Style\DrupalStyle;
use Drupal\Console\Helper\HelperTrait;
use Drupal\Core\Asset\LibraryDiscovery;
use GuzzleHttp\Client;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class InstallCommand.
 *
 * @package Drupal\ckeditor_media_embed
 *
 * @DrupalCommand (
 *     extension="ckeditor_media_embed",
 *     extensionType="module"
 * )
 */
class InstallCommand extends Command {

  use ContainerAwareCommandTrait;

  /**
   * @var CKEditorPluginManager
   */
  protected $ckeditorPluginManager;

  /**
   * @var Client
   */
  protected $httpClient;

  /**
   * @var LibraryDiscovery
   */
  protected $libraryDiscovery;

  /**
   * @var Site
   */
  protected $site;

  /**
   * @var FileSystem
   */
  protected $fileSystem;

  protected $packageVersion;

  /**
   * {@inheritdoc}
   */
  public function __construct(CKEditorPluginManager $ckeditorPluginManager, LibraryDiscovery $libraryDiscovery, Client $httpClient) {
    parent::__construct();

    $this->ckeditorPluginManager = $ckeditorPluginManager;
    $this->libraryDiscovery = $libraryDiscovery;
    $this->httpClient = $httpClient;

    $this->fileSystem = new Filesystem();
    $this->setPackageVersion();
  }

  /**
   * {@inheritdoc}
   */
  protected function configure() {
    $this
      ->setName('ckeditor_media_embed:install')
      ->setDescription($this->trans('commands.ckeditor_media_embed.install.description'));
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $io = new DrupalStyle($input, $output);

    $package_directory = $this->downloadCKEditorFull($io);

    foreach (AssetManager::getPlugins() as $plugin) {
      $this->installCKeditorPlugin($io, $package_directory, $plugin);
    }

    $this->ckeditorPluginManager->clearCachedDefinitions();
  }

  /**
   * Set the current CKEditor package version that is installed with Drupal.
   *
   * @return $this
   */
  protected function setPackageVersion() {
    $this->packageVersion = AssetManager::getCKEditorVersion($this->libraryDiscovery);

    return $this;
  }

  /**
   * Install an individual CKEditor plugin.
   *
   * @param DrupalStyle $io
   *   The Drupal i/o object.
   * @param string $package_directory
   *   The full path to the downloaded CKEditor full development package.
   * @param string $plugin_name
   *   The machine name of the CKEditor plugin to install.
   *
   * @return $this
   */
  // @codingStandardsIgnoreLine
  protected function installCKeditorPlugin(DrupalStyle $io, $package_directory, $plugin_name) {
    $libraries_path = AssetManager::getCKEditorLibraryPluginDirectory() . $plugin_name;
    $package_plugin_path = $package_directory . '/plugins/' . $plugin_name;

    try {
      $this->fileSystem->mkdir($libraries_path);
      $this->fileSystem->mirror($package_plugin_path, $libraries_path);

      $io->success(
        sprintf(
          $this->trans('commands.ckeditor_media_embed.install.messages.success-installed-plugin'),
          $plugin_name
        )
      );
    }
    catch (IOExceptionInterface $e) {
      $io->error($e->getMessage());
    }

    return $this;
  }

  /**
   * Download the full source package of CKEditor and extract it.
   *
   * @param DrupalStyle $io
   *   The Drupal i/o object.
   *
   * @return string
   *   The path to the downloaded and extracted package.
   */
  // @codingStandardsIgnoreLine
  protected function downloadCKEditorFull(DrupalStyle $io) {
    $io->comment(
      sprintf(
        $this->trans('commands.ckeditor_media_embed.install.messages.comment-downloading-package'),
        $this->packageVersion
      )
    );

    $package_name = AssetManager::getCKEditorDevFullPackageName($this->packageVersion);
    $package_url = AssetManager::getCKEditorDevFullPackageUrl($this->packageVersion);
    $package_directory = sys_get_temp_dir() . '/' . $package_name;
    $package_archive = sys_get_temp_dir() . "/$package_name.zip";

    try {
      $this->downloadFile($package_url, $package_archive);
      if (is_file($package_archive)) {
        $zippy = Zippy::load();
        $archive = $zippy->open($package_archive);
        $archive->extract(sys_get_temp_dir());
        $io->success(
          sprintf(
            $this->trans('commands.ckeditor_media_embed.install.messages.success-downloading-package'),
            $this->packageVersion
          )
        );
      }
    }
    catch (\Exception $e) {
      $io->error($e->getMessage());
    }

    return $package_directory;
  }

  public function downloadFile($url, $destination) {
    $this->httpClient->get($url, array('sink' => $destination));
    return file_exists($destination);
  }
}
