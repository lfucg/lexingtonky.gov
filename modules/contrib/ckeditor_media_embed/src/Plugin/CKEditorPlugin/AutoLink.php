<?php

namespace Drupal\ckeditor_media_embed\Plugin\CKEditorPlugin;

use Drupal\ckeditor_media_embed\AssetManager;
use Drupal\ckeditor_media_embed\CKEditorVersionAwarePluginBase;
use Drupal\editor\Entity\Editor;

/**
 * Defines the "Media Embed Base" plugin.
 *
 * @CKEditorPlugin(
 *   id = "autolink",
 *   label = @Translation("Auto Link"),
 *   module = "ckeditor_media_embed"
 * )
 */
class AutoLink extends CKEditorVersionAwarePluginBase {

  /**
   * {@inheritdoc}
   */
  public function getDependencies(Editor $editor) {
    $dependencies = [
      'link',
    ];

    if ($this->needsTextMatchDependency()) {
      $dependencies[] = 'textmatch';
    }

    return $dependencies;
  }

  /**
   * Determine if the textmatch plugin is needed as a dependency.
   *
   * @return bool
   *   Returns TRUE if the textmatch plugin is necessary.
   */
  public function needsTextMatchDependency() {
    return $this->versionCompare('4.11') >= 0;
  }

  /**
   * {@inheritdoc}
   */
  public function getLibraries(Editor $editor) {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function isInternal() {
    return FALSE;
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
