<?php

namespace Drupal\ckeditor_entity_link\Plugin\CKEditorPlugin;

use Drupal\ckeditor\CKEditorPluginBase;
use Drupal\editor\Entity\Editor;

/**
 * Defines the "entitylink" plugin.
 *
 * @CKEditorPlugin(
 *   id = "entitylink",
 *   label = @Translation("Entity link"),
 * )
 */
class EntityLink extends CKEditorPluginBase {

  /**
   * {@inheritdoc}
   */
  public function getFile() {
    return drupal_get_path('module', 'ckeditor_entity_link') . '/js/plugins/entitylink/plugin.js';
  }

  /**
   * {@inheritdoc}
   */
  public function getLibraries(Editor $editor) {
    return [
      'core/drupal.ajax',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getConfig(Editor $editor) {
    return [
      'EntityLink_dialogTitleAdd' => $this->t('Add Link'),
      'EntityLink_dialogTitleEdit' => $this->t('Edit Link'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getButtons() {
    $path = drupal_get_path('module', 'ckeditor_entity_link') . '/js/plugins/entitylink';
    return [
      'EntityLink' => [
        'label' => $this->t('Link'),
        'image' => $path . '/link.png',
      ],
    ];
  }

}
