<?php

namespace Drupal\ckeditor_media_embed\Plugin\CKEditorPlugin;

use Drupal\ckeditor_media_embed\AssetManager;

use Drupal\ckeditor\CKEditorPluginBase;
use Drupal\editor\Entity\Editor;

/**
 * Defines the "Media Embed" plugin.
 *
 * @CKEditorPlugin(
 *   id = "embed",
 *   label = @Translation("Media Embed"),
 *   module = "ckeditor_media_embed"
 * )
 */
class MediaEmbed extends CKEditorPluginBase {

  /**
   * {@inheritdoc}
   */
  public function getDependencies(Editor $editor) {
    return array(
      'embedbase',
      'notificationaggregator',
      'notification',
    );
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
  public function getButtons() {
    return array(
      'Embed' => array(
        'label' => t('Media Embed'),
        'image' => AssetManager::getCKEditorLibraryPluginPath() . $this->getPluginId() . '/icons/' . $this->getPluginId() . '.png',
      ),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getConfig(Editor $editor) {
    return array();
  }

}
