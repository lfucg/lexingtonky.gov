<?php

/**
 * @file
 * Contains \Drupal\term_reference_breadcrumb_builder\TermReferenceBreadcrumbBuilder.
 */

namespace Drupal\term_reference_breadcrumb_builder;

use Drupal\Core\Breadcrumb\BreadcrumbBuilderInterface;
use Drupal\Core\Breadcrumb\Breadcrumb;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Link;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Provides a custom taxonomy breadcrumb builder that uses the term hierarchy.
 * based off of taxonomy module TermBreadcrumbBuilder
 */
class TermReferenceBreadcrumbBuilder implements BreadcrumbBuilderInterface {
  use StringTranslationTrait;

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityManager;

  /**
   * The taxonomy storage.
   *
   * @var \Drupal\Taxonomy\TermStorageInterface
   */
  protected $termStorage;

  /**
   * Constructs the TermBreadcrumbBuilder.
   *
   * @param \Drupal\Core\Entity\EntityManagerInterface $entityManager
   *   The entity manager.
   */
  public function __construct(EntityManagerInterface $entityManager) {
    $this->entityManager = $entityManager;
    $this->termStorage = $entityManager->getStorage('taxonomy_term');
  }

  /**
   * {@inheritdoc}
   */
  public function applies(RouteMatchInterface $route_match) {
    return $route_match->getRouteName() == 'entity.node.canonical' &&
      $route_match->getParameter('node')->field_lex_site_nav &&
      $route_match->getParameter('node')->field_lex_site_nav->referencedEntities();
  }

  /**
   * {@inheritdoc}
   * based off of taxonomy module TermBreadcrumbBuilder
   */
  public function build(RouteMatchInterface $route_match) {
    $breadcrumb = new Breadcrumb();
    $breadcrumb->addLink(Link::createFromRoute($this->t('Home'), '<front>'));
    $term = $route_match->getParameter('node')->field_lex_site_nav->referencedEntities()[0];
    $breadcrumb->addCacheableDependency($term);
    $parents = $this->termStorage->loadAllParents($term->id());
    foreach (array_reverse($parents) as $term) {
      $term = $this->entityManager->getTranslationFromContext($term);
      $breadcrumb->addCacheableDependency($term);
      $breadcrumb->addLink(Link::createFromRoute($term->getName(), 'entity.taxonomy_term.canonical', array('taxonomy_term' => $term->id())));
    }
    $breadcrumb->addCacheContexts(['route']);
    return $breadcrumb;
  }
}
