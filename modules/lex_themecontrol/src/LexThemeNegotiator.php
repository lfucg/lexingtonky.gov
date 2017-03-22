<?php

namespace Drupal\lex_themecontrol;

use Drupal\Core\Theme\DefaultNegotiator;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\node\NodeInterface;

/**
 * Switches Theme according to the related department of the site.
 */
class LexThemeNegotiator extends DefaultNegotiator {

  /**
   * {@inheritdoc}
   */
  public function determineActiveTheme(RouteMatchInterface $route_match) {
    $node = $route_match->getParameter('node');

    if ($node instanceof NodeInterface) {
      try {
        $departments = $node->get('field_related_departments')->getValue();

        if (is_array($departments)) {
          foreach ($departments as $department) {
            switch($department['target_id']) {
              case 2: return 'lex_police';
              case 440: return 'lex_planning_commission';
            }
          }
        }
      }
      catch ( \InvalidArgumentException $e) {}
    }

    return NULL;
  }

}