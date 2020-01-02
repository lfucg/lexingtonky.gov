<?php

namespace grasmash\DrupalSecurityWarning\Tests;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

class SecurityWarningTest extends TestCase
{

  /**
   * Tests Composer output.
   */
  public function testComposerOutput() {
    $fs = new Filesystem();
    $tmp_dir = __DIR__ . "/../tmp";
    $fs->remove($tmp_dir);
    $fs->mkdir($tmp_dir);
    $fs->copy(__DIR__ . "/../fixtures/example.composer.json", $tmp_dir . "/composer.json");

    $bin_dir = __DIR__ . "/../../vendor/bin";
    $process = new Process("$bin_dir/composer install --working-dir=$tmp_dir -v");
    $process->setTimeout(600);
    $process->run();
    $output = $process->getOutput();

    $this->assertContains("You are using Drupal packages that are not supported by the Drupal Security Team!", $output);
    $this->assertContains("- drupal/ctools:3.0.0.0-alpha27: Alpha releases are not covered by Drupal security advisories.", $output);
    $this->assertContains("See https://www.drupal.org/security-advisory-policy for more information.", $output);
    $this->assertNotContains("- drupal/token:1.0.0", $output);
  }

}
