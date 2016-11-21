<?php

namespace Drupal\pager_example\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Connection;
use Drupal\Core\Database\Query\PagerSelectExtender;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Controller for pager_example.page route.
 *
 * This is an example describing how a module can implement a pager in order to
 * reduce the number of output rows to the screen and allow a user to scroll
 * through multiple screens of output.
 *
 * @see http://drupal.org/developing/api/database
 * @see http://drupal.org/node/508796
 */
class PagerExamplePage extends ControllerBase {

  /**
   * The database object.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * Entity storage for node entities.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $nodeStorage;

  /**
   * PagerExamplePage constructor.
   *
   * @param \Drupal\Core\Database\Connection $database
   *   The database.
   * @param \Drupal\Core\Entity\EntityStorageInterface $node_storage
   *   Entity storage for node entities.
   */
  public function __construct(Connection $database, EntityStorageInterface $node_storage) {
    $this->database = $database;
    $this->nodeStorage = $node_storage;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $controller = new static(
      $container->get('database'),
      $container->get('entity_type.manager')->getStorage('node')
    );
    $controller->setStringTranslation($container->get('string_translation'));
    return $controller;
  }

  /**
   * Content callback for the pager_example.page route.
   */
  public function getContent() {
    // First we'll tell the user what's going on. This content can be found
    // in the twig template file: templates/description.html.twig.
    // @todo: Set up links to create nodes and point to devel module.
    $build = [
      'description' => [
        '#theme' => 'pager_example_description',
        '#description' => 'foo',
        '#attributes' => [],
      ],
    ];

    // We need to count the number of nodes so that we can tell the user to add
    // some if there aren't any.
    $count_nodes = $this->database->select('node', 'n')
      ->countQuery()
      ->execute()
      ->fetchField();

    if ($count_nodes == 0) {
      $build['no-nodes'] = [
        '#markup' => $this->t('No node for output. Please <a href="@url">create a node</a>.',
          array(
            '@url' => Url::fromRoute('node.add_page'),
          )
        ),
      ];
      return $build;
    }

    // Now we want to get our tabular data. We select nodes from node table
    // limited by 2 per page and orderby DESC because we want to show newest
    // node first.
    $pager_data = $this->database->select('node', 'n')
      ->extend(PagerSelectExtender::class)
      ->fields('n', array('nid'))
      ->orderBy('n.nid', 'DESC')
      ->limit(2)
      ->execute()
      ->fetchAllAssoc('nid');

    $nodes = $this->nodeStorage->loadMultiple(array_keys($pager_data));

    // We are going to output the results in a table so we set up the rows.
    $rows = [];
    foreach ($nodes as $node) {
      $rows[] = array(
        'nid' => $node->id(),
        'title' => $node->getTitle(),
      );
    }

    // Build a render array which will be themed as a table with a pager.
    $build['pager_example'] = array(
      '#rows' => $rows,
      '#header' => array(t('NID'), t('Title')),
      '#type' => 'table',
      '#empty' => t('No content available.'),
    );
    $build['pager'] = array(
      '#type' => 'pager',
      '#weight' => 10,
    );

    return $build;
  }

}
