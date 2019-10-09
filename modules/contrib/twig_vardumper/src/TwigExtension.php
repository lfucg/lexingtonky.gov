<?php

namespace Drupal\twig_vardumper;

/**
 * Twig extension with some useful functions and filters.
 */
class TwigExtension extends \Twig_Extension {

  /**
   * {@inheritdoc}
   */
  public function getFunctions() {

    return [
      new \Twig_SimpleFunction('dump', [$this, 'drupalDump'], [
        'needs_context' => TRUE,
        'needs_environment' => TRUE,
      ]),
      new \Twig_SimpleFunction('vardumper', [$this, 'drupalDump'], [
        'needs_context' => TRUE,
        'needs_environment' => TRUE,
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
   * @param \Twig_Environment $env
   *   Enviroment values.
   * @param array $context
   *   Context values.
   */
  public function drupalDump(\Twig_Environment $env, array $context) {

    if (!$env->isDebug()) {
      return;
    }

    $var_dumper = '\Symfony\Component\VarDumper\VarDumper';
    if (class_exists($var_dumper)) {
      $count = func_num_args();
      if (2 === $count) {
        $vars = [];
        foreach ($context as $key => $value) {
          if (!$value instanceof \Twig_Template) {
            $vars[$key] = $value;
          }
        }
        call_user_func($var_dumper . '::dump', $vars);
      }
      else {
        for ($i = 2; $i < $count; ++$i) {
          call_user_func($var_dumper . '::dump', func_get_arg($i));
        }
      }
    }
    else {
      trigger_error('Could not dump the variable because symfony/var-dumper component is not installed.', E_USER_WARNING);
    }
  }

}
