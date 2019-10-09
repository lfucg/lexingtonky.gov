<?php

namespace Drupal\ckeditor_media_embed\Plugin\CKEditorPlugin;

use Drupal\ckeditor_media_embed\AssetManager;

use Drupal\Core\Plugin\PluginBase;
use Drupal\editor\Entity\Editor;
use Drupal\ckeditor\CKEditorPluginInterface;
use Drupal\ckeditor\CKEditorPluginConfigurableInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Defines the "Media Embed Base" plugin.
 *
 * @CKEditorPlugin(
 *   id = "embedbase",
 *   label = @Translation("Media Embed Base"),
 *   module = "ckeditor_media_embed"
 * )
 */
class MediaEmbedBase extends PluginBase implements CKEditorPluginInterface, CKEditorPluginConfigurableInterface {

  /**
   * {@inheritdoc}
   */
  public function getDependencies(Editor $editor) {
    return array('notificationaggregator');
  }

  /**
   * {@inheritdoc}
   */
  public function getLibraries(Editor $editor) {
    return array();
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
    $config = [];

    $config['embed_provider'] = \Drupal::config('ckeditor_media_embed.settings')->get('embed_provider');

    return $config;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state, Editor $editor) {
    $settings = $editor->getSettings();

    $form['settings_info'] = array(
      '#markup' => $this->t('Settings for the Media Embed and Semantic Media Embed plugins are located on the @link.',
      array('@link' => \Drupal::service('ckeditor_media_embed')->getSettingsLink())),
    );

    return $form;
  }

}
