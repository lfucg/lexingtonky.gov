<?php

namespace Drupal\current_page_crumb;

use Drupal\Core\Link;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Component\Utility\Unicode;
use Drupal\system\PathBasedBreadcrumbBuilder;
use Symfony\Cmf\Component\Routing\RouteObjectInterface;

/**
 * Adds the current page title to the breadcrumb.
 *
 * Extend PathBased Breadcrumbs to include the current page title as an unlinked
 * crumb. The module uses the path if the title is unavailable and it excludes
 * all admin paths.
 *
 * {@inheritdoc}
 */
class BreadcrumbBuilder extends PathBasedBreadcrumbBuilder {

  /**
   * {@inheritdoc}
   */
  public function build(RouteMatchInterface $route_match) {
    $breadcrumbs = parent::build($route_match);

    $request = \Drupal::request();
    $path = trim($this->context->getPathInfo(), '/');
    $path_elements = explode('/', $path);
    $route = $request->attributes->get(RouteObjectInterface::ROUTE_OBJECT);

    // Do not adjust the breadcrumbs on admin paths.
    if ($route && !$route->getOption('_admin_route')) {
      $title = $this->titleResolver->getTitle($request, $route);
      if (!isset($title)) {

        // Fallback to using the raw path component as the title if the
        // route is missing a _title or _title_callback attribute.
        $title = str_replace(array('-', '_'), ' ', Unicode::ucfirst(end($path_elements)));
      }
      $breadcrumbs->addLink(Link::createFromRoute($title, '<none>'));
    }

    // Add the full URL path as a cache context, since we will display the
    // current page as part of the breadcrumb.
    $breadcrumbs->addCacheContexts(['url.path']);

    return $breadcrumbs;
  }

}
