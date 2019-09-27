<?php

namespace Drupal\components\Template;

/**
 * Generates a render array and renders that.
 *
 * @code
 *  {%
 *    theme 'item_list' with {
 *      items: [],
 *    }
 *  %}
 * @endcode
 */
class TwigThemeTokenParser extends \Twig_TokenParser {

  /**
   * {@inheritdoc}
   */
  public function parse(\Twig_Token $token) {
    $expr = $this->parser->getExpressionParser()->parseExpression();

    return new TwigNodeTheme($expr, $this->parseArguments(), $token->getLine(), $this->getTag());
  }

  /**
   * Parses the arguments.
   *
   * @return \Twig_Node_Expression
   *   A node expression containing the variables list.
   */
  protected function parseArguments() {
    $stream = $this->parser->getStream();

    $variables = NULL;
    if ($stream->nextIf(\Twig_Token::NAME_TYPE, 'with')) {
      $variables = $this->parser->getExpressionParser()->parseExpression();
    }

    $stream->expect(\Twig_Token::BLOCK_END_TYPE);

    return $variables;
  }

  /**
   * {@inheritdoc}
   */
  public function getTag() {
    return 'theme';
  }

}
