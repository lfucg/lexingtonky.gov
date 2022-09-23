<?php

namespace Drupal\imce\Plugin\CKEditorPlugin;

use Drupal\editor\Entity\Editor;
use Drupal\ckeditor\CKEditorPluginBase;

/**
 * Defines Imce plugin for CKEditor.
 *
 * @CKEditorPlugin(
 *   id = "imce",
 *   label = "Imce File Manager"
 * )
 */
class Imce extends CKEditorPluginBase {

  /**
   * {@inheritdoc}
   */
  public function getDependencies(Editor $editor) {
    // Need drupalimage for drupallink support. See #2666596 .
    return ['drupalimage', 'drupalimagecaption'];
  }

  /**
   * {@inheritdoc}
   */
  public function getFile() {
    return drupal_get_path('module', 'imce') . '/js/plugins/ckeditor/imce.ckeditor.js';
  }

  /**
   * {@inheritdoc}
   */
  public function getButtons() {
    return [
      'ImceImage' => [
        'label' => $this->t('Insert images using Imce File Manager'),
        'image' => $this->imageIcon(),
      ],
      'ImceLink' => [
        'label' => $this->t('Insert file links using Imce File Manager'),
        'image' => $this->linkIcon(),
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getConfig(Editor $editor) {
    return [
      'ImceImageIcon' => file_create_url($this->imageIcon()),
      'ImceLinkIcon' => file_create_url($this->linkIcon()),
    ];
  }

  /**
   * Returns image icon path.
   *
   * Uses the icon from drupalimage plugin.
   */
  public function imageIcon() {
    return drupal_get_path('module', 'imce') . '/js/plugins/ckeditor/icons/imceimage.png';
  }

  /**
   * Returns link icon path.
   *
   * Uses the icon from drupallink plugin.
   */
  public function linkIcon() {
    return drupal_get_path('module', 'imce') . '/js/plugins/ckeditor/icons/imcelink.png';
  }

}
