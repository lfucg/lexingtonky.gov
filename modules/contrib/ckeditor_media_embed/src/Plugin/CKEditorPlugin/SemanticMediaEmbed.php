<?php

namespace Drupal\ckeditor_media_embed\Plugin\CKEditorPlugin;

use Drupal\ckeditor_media_embed\AssetManager;

use Drupal\ckeditor\CKEditorPluginBase;
use Drupal\editor\Entity\Editor;

/**
 * Defines the "Semantic Media Embed" plugin.
 *
 * @CKEditorPlugin(
 *   id = "embedsemantic",
 *   label = @Translation("Semantic Media Embed"),
 *   module = "ckeditor_media_embed"
 * )
 */
class SemanticMediaEmbed extends CKEditorPluginBase {

  /**
   * {@inheritdoc}
   */
  public function getDependencies(Editor $editor) {
    return [
      'embedbase',
      'notificationaggregator',
      'notification',
    ];
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
    return [
      'EmbedSemantic' => [
        'label' => $this->t('Semantic Media Embed'),
        'image' => AssetManager::getCKEditorLibraryPluginPath() . $this->getPluginId() . '/icons/' . $this->getPluginId() . '.png',
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getConfig(Editor $editor) {
    return [];
  }

}
