<?php

namespace Drupal\components\Template;

/**
 * Represents a call to theme() as a Twig node.
 */
class TwigNodeTheme extends \Twig_Node implements \Twig_NodeOutputInterface {

  public function __construct(\Twig_Node_Expression $theme, \Twig_Node_Expression $variables = NULL, $lineno, $tag = NULL) {
    parent::__construct(
      ['theme' => $theme, 'variables' => $variables],
      [],
      $lineno,
      $tag
    );
  }

  /**
   * Compiles the template.
   *
   * @param \Twig_Compiler $compiler
   *   The compiler.
   */
  public function compile(\Twig_Compiler $compiler) {
    $expression = new \Twig_Node_Expression_Function(
      'theme',
      new \Twig_Node([$this->getNode('theme'), $this->getNode('variables')]),
      $this->getLine()
    );

    $compiler->addDebugInfo($this)
      ->subcompile(new \Twig_Node_Print($expression, $this->getLine()));
  }

}
