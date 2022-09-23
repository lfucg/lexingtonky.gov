<?php

namespace Drupal\Tests\upgrade_status\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\upgrade_status\DeprecationMessage;

/**
 * Tests analysing Twig templates.
 *
 * @group upgrade_status
 */
final class TwigDeprecationAnalyzerTest extends KernelTestBase {

  protected static $modules = [
    'upgrade_status',
    'upgrade_status_test_twig',
  ];

  public function testDeprecationReport() {
    $extension = $this->container->get('module_handler')->getModule('upgrade_status_test_twig');

    $sut = $this->container->get('upgrade_status.twig_deprecation_analyzer');
    $twig_deprecations = $sut->analyze($extension);

    if (version_compare(\Drupal::VERSION, '9.0.0') === -1) {
      $this->assertCount(1, $twig_deprecations, var_export($twig_deprecations, TRUE));
      $this->assertEquals(new DeprecationMessage(
        'Twig Filter "deprecatedfilter" is deprecated. See https://drupal.org/node/3071078.',
        'modules/contrib/upgrade_status/tests/modules/upgrade_status_test_twig/templates/test.html.twig',
        '10'
      ), $twig_deprecations[0]);
    }
    else {
      $this->assertCount(2, $twig_deprecations, var_export($twig_deprecations, TRUE));
      $this->assertContainsEquals(new DeprecationMessage(
        'Twig Filter "deprecatedfilter" is deprecated. See https://drupal.org/node/3071078.',
        'modules/contrib/upgrade_status/tests/modules/upgrade_status_test_twig/templates/test.html.twig',
        '10'
      ), $twig_deprecations);
      $this->assertContainsEquals(new DeprecationMessage(
        'The spaceless tag in "modules/contrib/upgrade_status/tests/modules/upgrade_status_test_twig/templates/spaceless.html.twig" at line 2 is deprecated since Twig 2.7, use the "spaceless" filter with the "apply" tag instead. See https://drupal.org/node/3071078.',
        'modules/contrib/upgrade_status/tests/modules/upgrade_status_test_twig/templates/spaceless.html.twig',
        0
      ), $twig_deprecations);
    }

  }

}
