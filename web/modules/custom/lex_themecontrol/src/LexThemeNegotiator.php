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
    if (\Drupal::service('path.matcher')->isFrontPage()) {
      return 'lex_home';
    }
    else {
      $node = $route_match->getParameter('node');

      if ($node instanceof NodeInterface) {
        try {
          $departments = $node->get('field_related_departments')->getValue();

          if (is_array($departments)) {
            foreach ($departments as $department) {
              switch ($department['target_id']) {
                case 2:

                  return 'lex_police';

                case 440:

                  return 'lex_planning_commission';

                case 1226:

                  return 'lex_economic_development';
              }
            }
          }
        }
        catch (\InvalidArgumentException $e) {

        }
      }
    }
    return NULL;
  }

}
