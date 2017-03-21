<?php

namespace Drupal\ckeditor_media_embed\Command;

use Drupal\ckeditor_media_embed\AssetManager;

use Drupal\Console\Command\ContainerAwareCommand as BaseCommand;
use Drupal\Console\Helper\HelperTrait;
use Drupal\Console\Style\DrupalStyle;
use Alchemy\Zippy\Zippy;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class InstallCommand.
 *
 * @package Drupal\ckeditor_media_embed
 */
class InstallCommand extends BaseCommand {

  use HelperTrait;

  protected $packageVersion;

  /**
   * {@inheritdoc}
   */
  public function __construct(HelperSet $helper_set) {
    parent::__construct($helper_set);

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

    $this->getService('plugin.manager.ckeditor.plugin')->clearCachedDefinitions();
  }

  /**
   * Set the current CKEditor package version that is installed with Drupal.
   *
   * @return $this
   */
  protected function setPackageVersion() {
    $this->packageVersion = AssetManager::getCKEditorVersion($this->getService('library.discovery'));

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
      $this->getContainerHelper()->get('filesystem')->mkdir($libraries_path);
      $this->getContainerHelper()->get('filesystem')->mirror($package_plugin_path, $libraries_path);

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
      $this->getHttpClientHelper()->downloadFile($package_url, $package_archive);
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

}
