<?php

namespace Drupal\imce\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Class ImceAdminBrowserController.
 */
class ImceAdminBrowserController extends ControllerBase {

  /**
   * Browser Page.
   *
   * @return string
   *   Return Hello string.
   */
  public function page() {
    $render['iframe'] = [
      '#type' => 'inline_template',
      '#template' => '<iframe class="imce-browser" src="{{ url }}"></iframe>',
      '#context' => [
        'url' => '/imce',
      ],
    ];
    $render['#attached']['library'][] = 'imce/drupal.imce.admin';
    return $render;
  }

}
