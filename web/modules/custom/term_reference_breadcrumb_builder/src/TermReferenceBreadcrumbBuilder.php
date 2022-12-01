<?php

/**
 * @file
 * Contains \Drupal\term_reference_breadcrumb_builder\TermReferenceBreadcrumbBuilder.
 */

namespace Drupal\term_reference_breadcrumb_builder;

use Drupal\Core\Breadcrumb\BreadcrumbBuilderInterface;
use Drupal\Core\Breadcrumb\Breadcrumb;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
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
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityManager;

    /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityRepositoryInterface
   */
  protected $entityRepository;

  /**
   * The taxonomy storage.
   */
  protected $termStorage;

  /**
   * Constructs the TermBreadcrumbBuilder.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityManager
   *   The entity manager.
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entityRepository
   *   The entity respository interface.
   */
  public function __construct(EntityTypeManagerInterface $entityManager, EntityRepositoryInterface $entityRepository) {
    $this->entityManager = $entityManager;
    $this->entityRepository = $entityRepository;
    $this->termStorage = $entityManager->getStorage('taxonomy_term');
  }

  /**
   * {@inheritdoc}
   */
  public function applies(RouteMatchInterface $route_match) {
    if ($route_match->getRouteName() != 'entity.node.canonical') { return false; }

    $usesSiteNav = ($route_match->getParameter('node')->field_lex_site_nav &&
      $route_match->getParameter('node')->field_lex_site_nav->referencedEntities());

    $isOrgPage = ($route_match->getParameter('node')->field_organization_taxonomy_term &&
      $route_match->getParameter('node')->field_organization_taxonomy_term->referencedEntities());

    return ($usesSiteNav || $isOrgPage);
  }

  /**
   * {@inheritdoc}
   * based off of taxonomy module TermBreadcrumbBuilder
   */
  public function build(RouteMatchInterface $route_match) {
    $breadcrumb = new Breadcrumb();
    $breadcrumb->addLink(Link::createFromRoute($this->t('Home'), '<front>'));
    $termId = 0;
    if ($route_match->getParameter('node')->field_lex_site_nav->referencedEntities()) {
      $term = $route_match->getParameter('node')->field_lex_site_nav->referencedEntities()[0];
      $termId = $term->id();
      $breadcrumb->addCacheableDependency($term);
    } elseif ($route_match->getParameter('node')->field_organization_taxonomy_term) {
      // org pages default to 'departments and programs' term
      $termId = 294;
    }
    $parents = $this->termStorage->loadAllParents($termId);
    foreach (array_reverse($parents) as $term) {
      $term = $this->entityRepository->getTranslationFromContext($term);
      $breadcrumb->addCacheableDependency($term);
      $breadcrumb->addLink(Link::createFromRoute($term->getName(), 'entity.taxonomy_term.canonical', array('taxonomy_term' => $term->id())));
    }
    $breadcrumb->addCacheContexts(['route']);
    return $breadcrumb;
  }
}
