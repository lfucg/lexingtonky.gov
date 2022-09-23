<?php

namespace Drupal\devel\Routing;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Routing\RouteProviderInterface;
use Drupal\Core\Routing\RouteSubscriberBase;
use Drupal\Core\Routing\RoutingEvents;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * Subscriber for Devel routes.
 *
 * @see \Drupal\devel\Controller\EntityDebugController
 * @see \Drupal\devel\Plugin\Derivative\DevelLocalTask
 */
class RouteSubscriber extends RouteSubscriberBase {

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The router service.
   *
   * @var \Symfony\Component\Routing\RouterInterface
   */
  protected $routeProvider;

  /**
   * Constructs a new RouteSubscriber object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_manager
   *   The entity type manager.
   * @param \Symfony\Component\Routing\RouterInterface $router_provider
   *   The router service.
   */
  public function __construct(EntityTypeManagerInterface $entity_manager, RouteProviderInterface $router_provider) {
    $this->entityTypeManager = $entity_manager;
    $this->routeProvider = $router_provider;
  }

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    foreach ($this->entityTypeManager->getDefinitions() as $entity_type_id => $entity_type) {
      if ($route = $this->getEntityLoadRoute($entity_type)) {
        $collection->add("entity.$entity_type_id.devel_load", $route);
      }
      if ($route = $this->getEntityRenderRoute($entity_type)) {
        $collection->add("entity.$entity_type_id.devel_render", $route);
      }
      if ($route = $this->getEntityTypeDefinitionRoute($entity_type)) {
        $collection->add("entity.$entity_type_id.devel_definition", $route);
      }
    }
  }

  /**
   * Gets the entity load route.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type.
   *
   * @return \Symfony\Component\Routing\Route|null
   *   The generated route, if available.
   */
  protected function getEntityLoadRoute(EntityTypeInterface $entity_type) {
    if ($devel_load = $entity_type->getLinkTemplate('devel-load')) {
      // Set entities route parameters for the give template.
      $parameters = $this->getRouteParameters($entity_type->getLinkTemplate('edit-form'));
      $route = new Route($devel_load);
      $route
        ->addDefaults([
          '_controller' => '\Drupal\devel\Controller\EntityDebugController::entityLoad',
          '_title' => 'Devel Load',
        ])
        ->addRequirements([
          '_permission' => 'access devel information',
        ])
        ->setOption('_admin_route', TRUE)
        ->setOption('_devel_entity_type_id', $entity_type->id())
        ->setOption('parameters', $parameters);

      return $route;
    }
  }

  /**
   * Gets the entity render route.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type.
   *
   * @return \Symfony\Component\Routing\Route|null
   *   The generated route, if available.
   */
  protected function getEntityRenderRoute(EntityTypeInterface $entity_type) {
    if ($devel_render = $entity_type->getLinkTemplate('devel-render')) {
      $route = new Route($devel_render);
      // Set entities route parameters for the give template.
      $parameters = $this->getRouteParameters($entity_type->getLinkTemplate('canonical'));
      $route
        ->addDefaults([
          '_controller' => '\Drupal\devel\Controller\EntityDebugController::entityRender',
          '_title' => 'Devel Render',
        ])
        ->addRequirements([
          '_permission' => 'access devel information',
        ])
        ->setOption('_admin_route', TRUE)
        ->setOption('_devel_entity_type_id', $entity_type->id())
        ->setOption('parameters', $parameters);

      return $route;
    }
  }

  /**
   * Gets the entity type definition route.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type.
   *
   * @return \Symfony\Component\Routing\Route|null
   *   The generated route, if available.
   */
  protected function getEntityTypeDefinitionRoute(EntityTypeInterface $entity_type) {
    if ($devel_definition = $entity_type->getLinkTemplate('devel-definition')) {
      $entity_Link = $entity_type->getLinkTemplate('edit-form');
      if (empty($entity_Link)) {
        $entity_Link = $entity_type->getLinkTemplate('canonical');
      }
      // Set entities route parameters for the given template.
      $parameters = $this->getRouteParameters($entity_Link);
      $route = new Route($devel_definition);
      $route
        ->addDefaults([
          '_controller' => '\Drupal\devel\Controller\EntityDebugController::entityTypeDefinition',
          '_title' => 'Entity type definition',
        ])
        ->addRequirements([
          '_permission' => 'access devel information',
        ])
        ->setOption('_admin_route', TRUE)
        ->setOption('_devel_entity_type_id', $entity_type->id())
        ->setOption('parameters', $parameters);

      return $route;
    }
  }

  /**
   * Get route parameters from the template.
   *
   * @param string $entity_path
   *   Entity path.
   *
   * @return array
   *   List of parameters.
   */
  protected function getRouteParameters($entity_path) {
    $parameters = [];
    if ($entity_path && preg_match_all('/{\w*}/', $entity_path, $matches)) {
      foreach ($matches[0] as $match) {
        $match = str_replace(['{', '}'], ['', ''], $match);
        $parameters[$match] = [
          'type' => "entity:{$match}",
          'converter' => 'paramconverter.entity',
        ];
      }
    }

    return $parameters;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events = parent::getSubscribedEvents();
    $events[RoutingEvents::ALTER] = ['onAlterRoutes', 100];
    return $events;
  }

}
