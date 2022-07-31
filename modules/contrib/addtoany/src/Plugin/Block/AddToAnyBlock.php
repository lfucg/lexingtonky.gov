<?php

namespace Drupal\addtoany\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;

/**
 * Provides an 'AddToAny' block.
 *
 * @Block(
 *   id = "addtoany_block",
 *   admin_label = @Translation("AddToAny buttons"),
 * )
 */
class AddToAnyBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];
    $node_id = \Drupal::routeMatch()->getParameter('node');
    if (is_numeric($node_id)) {
      $node = Node::load($node_id);
    }

    $is_node = isset($node) && $node instanceof NodeInterface ? true : false;
    $data = $is_node ? addtoany_create_entity_data($node) : addtoany_create_data();

    $build = [
      '#addtoany_html'              => $data['addtoany_html'],
      '#link_url'                   => $data['link_url'],
      '#link_title'                 => $data['link_title'],
      '#button_setting'             => $data['button_setting'],
      '#button_image'               => $data['button_image'],
      '#universal_button_placement' => $data['universal_button_placement'],
      '#buttons_size'               => $data['buttons_size'],
      '#theme'                      => 'addtoany_standard',
      '#cache'                      => [
        'contexts' => ['url'],
      ],
    ];

    if ($is_node) {
      $build['#addtoany_html'] = \Drupal::token()->replace($data['addtoany_html'], ['node' => $node]);
    }

    return $build;
  }

}
