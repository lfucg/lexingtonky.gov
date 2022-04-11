<?php

/**
 * @file
 * Contains \Drupal\composer_deploy\ComposerDeployHanlder.
 */

namespace Drupal\composer_deploy;

class ComposerDeployHandler {

  protected $packages = [];

  /**
   * List of package prefixes.
   *
   * @var string[]
   */
  protected $prefixes = ['drupal'];

  public function __construct($path) {
    $packages = json_decode(file_get_contents($path), TRUE);
    // Composer 2.0 compatibility.
    // @see https://getcomposer.org/upgrade/UPGRADE-2.0.md
    $packages = $packages['packages'] ?? $packages;
    $this->packages = is_array($packages) ? $packages : [];
  }

  public function getPackage($projectName) {
    foreach ($this->packages as $package) {
      foreach ($this->prefixes as $prefix) {
        if ($package['name'] == $prefix . '/' . $projectName) {
          return $package;
        }
      }
    }
    return FALSE;
  }

  public static function fromVendorDir($vendor_dir) {
    return new static($vendor_dir . '/composer/installed.json');
  }

  /**
   * Set the package prefixes to check against.
   *
   * @param string[] $prefixes
   */
  public function setPrefixes(array $prefixes) {
    $this->prefixes = $prefixes;
  }

}
