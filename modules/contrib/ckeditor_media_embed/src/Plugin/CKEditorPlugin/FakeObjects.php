<?php

namespace Drupal\ckeditor_media_embed\Plugin\CKEditorPlugin;

use Drupal\ckeditor\CKEditorPluginBase;
use Drupal\ckeditor_media_embed\AssetManager;
use Drupal\editor\Entity\Editor;

/**
 * Defines the "Media Embed Base" plugin.
 *
 * @CKEditorPlugin(
 *   id = "fakeobjects",
 *   label = @Translation("Fake Objects"),
 *   module = "ckeditor_media_embed"
 * )
 */
class FakeObjects extends CKEditorPluginBase {

  /**
   * {@inheritdoc}
   */
  public function getButtons() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getFile() {
    return AssetManager::getCKEditorLibraryPluginPath() . $this->getPluginId() . '/plugin.js';
  }

  /**
   * {@inheritdoc}
   */
  public function getConfig(Editor $editor) {
    return [];
  }

}
