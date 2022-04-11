<?php

namespace Drupal\twig_vardumper;

use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Twig extension with some useful functions and filters.
 */
class TwigExtension extends AbstractExtension {

  /**
   * {@inheritdoc}
   */
  public function getFunctions() {
    return [
      new TwigFunction('dump', [$this, 'drupalDump'], [
        'is_safe' => ['html'],
        'needs_context' => TRUE,
        'needs_environment' => TRUE,
        'is_variadic' => TRUE,
      ]),
      new TwigFunction('vardumper', [$this, 'drupalDump'], [
        'is_safe' => ['html'],
        'needs_context' => TRUE,
        'needs_environment' => TRUE,
        'is_variadic' => TRUE,
      ]),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return 'twig_vardumper';
  }

  /**
   * Dumps information about variables.
   *
   * @param \Twig\Environment $env
   *   Enviroment values.
   * @param array $context
   *   Context values.
   * @param array $args
   *   Variables.
   *
   * @return false|string|void
   */
  public function drupalDump(Environment $env, array $context, array $args = []) {

    if (!$env->isDebug()) {
      return;
    }

    ob_start();
    $var_dumper = '\Symfony\Component\VarDumper\VarDumper';
    if (class_exists($var_dumper)) {
      if (!empty($args)) {
        foreach ($args as $arg) {
          call_user_func($var_dumper . '::dump', $arg);
        }
      }
      else {
        call_user_func($var_dumper . '::dump', $context);
      }
      return ob_get_clean();
    }
    else {
      trigger_error('Could not dump the variable because symfony/var-dumper component is not installed.', E_USER_WARNING);
    }
  }

}
