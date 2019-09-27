<?php

namespace Drupal\components\Template;

use Drupal\Core\Render\RendererInterface;

/**
 * A class providing components's Twig extensions.
 */
class TwigExtension extends \Twig_Extension {

  /**
   * The renderer.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * TwigExtension constructor.
   *
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer.
   */
  public function __construct(RendererInterface $renderer) {
    $this->renderer = $renderer;
  }

  /**
   * {@inheritdoc}
   */
  public function getTokenParsers() {
    return [
      new TwigThemeTokenParser(),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFunctions() {
    return [
      new \Twig_SimpleFunction('theme', [$this, 'theme']),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return 'components';
  }

  /**
   * Callback for theme function in Twig.
   *
   * @param string $theme
   *   The theme definition key.
   * @param array $variables
   *   The variables passed for rendering.
   *
   * @return \Drupal\Component\Render\MarkupInterface
   *   The renderer markup.
   */
  public function theme($theme, array $variables) {
    $render_array = ['#theme' => $theme];
    foreach ($variables as $key => $variable) {
      $render_array['#' . $key] = $variable;
    }

    return $this->render($render_array);
  }

  /**
   * Renders a render array.
   *
   * @param array $render_array
   *   The render array.
   *
   * @return \Drupal\Component\Render\MarkupInterface
   *   The renderer markup.
   */
  protected function render(array $render_array) {
    // This is a render array, with special simple cases already handled.
    // Early return if this element was pre-rendered (no need to re-render).
    if (isset($render_array['#printed']) && $render_array['#printed'] == TRUE && isset($render_array['#markup']) && strlen($render_array['#markup']) > 0) {
      return $render_array['#markup'];
    }

    $render_array['#printed'] = FALSE;

    return $this->renderer->render($render_array);
  }

}
