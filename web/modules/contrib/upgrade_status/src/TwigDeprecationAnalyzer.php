<?php

namespace Drupal\upgrade_status;

use Drupal\Core\Extension\Extension;
use Drupal\Core\Template\TwigEnvironment;
use Twig\Util\DeprecationCollector;
use Twig\Util\TemplateDirIterator;

class TwigDeprecationAnalyzer {

  /**
   * The Twig environment.
   *
   * @var \Drupal\Core\Template\TwigEnvironment
   */
  protected $twigEnvironment;

  public function __construct(TwigEnvironment $twig_environment) {
    $this->twigEnvironment = $twig_environment;
  }

  /**
   * Analyzes theme functions in an extension.
   *
   * @param \Drupal\Core\Extension\Extension $extension
   *   The extension to be analyzed.
   *
   * @return \Drupal\upgrade_status\DeprecationMessage[]
   */
  public function analyze(Extension $extension): array {
    $deprecations = array_map(static function (string $deprecation) {
      $file_matches = [];
      $line_matches = [];
      preg_match('/([a-zA-Z0-9\_\-\/]+.html\.twig)/', $deprecation, $file_matches);
      preg_match('/(\d+).?$/', $deprecation, $line_matches);
      $message = preg_replace('! in (.+)\.twig at line \d+\.!', '.', $deprecation);
      $message .= ' See https://drupal.org/node/3071078.';
      return new DeprecationMessage(
        $message,
        $file_matches[1],
        $line_matches[1] ?? 0
      );
    }, $this->collectDeprecations($extension->getPath()));
    // Ensure files are sorted properly.
    usort($deprecations, static function (DeprecationMessage $a, DeprecationMessage $b) {
      return strcmp($a->getFile(), $b->getFile());
    });
    return $deprecations;
  }

  /**
   * Analyzes twig templates for calls of deprecated code.
   *
   * @param $directory
   *   The directory which Twig templates should be analyzed.
   *
   * @return array
   */
  protected function collectDeprecations(string $directory): array {
    $iterator = new TemplateDirIterator(
      new TwigRecursiveIterator($directory)
    );
    return (new DeprecationCollector($this->twigEnvironment))
      ->collect($iterator);
  }

}
