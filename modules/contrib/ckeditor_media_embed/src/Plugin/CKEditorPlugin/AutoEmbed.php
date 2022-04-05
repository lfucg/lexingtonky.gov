<?php

namespace Drupal\ckeditor_media_embed\Plugin\CKEditorPlugin;

use Drupal\ckeditor_media_embed\AssetManager;
use Drupal\ckeditor_media_embed\CKEditorVersionAwarePluginBase;

use Drupal\editor\Entity\Editor;
use Drupal\ckeditor\CKEditorPluginConfigurableInterface;
use Drupal\ckeditor\CKEditorPluginContextualInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Defines the "Auto Embed" plugin.
 *
 * @CKEditorPlugin(
 *   id = "autoembed",
 *   label = @Translation("Auto Embed"),
 *   module = "ckeditor_media_embed"
 * )
 */
class AutoEmbed extends CKEditorVersionAwarePluginBase implements CKEditorPluginConfigurableInterface, CKEditorPluginContextualInterface {

  /**
   * {@inheritdoc}
   */
  public function getDependencies(Editor $editor) {
    $settings = $editor->getSettings();

    $dependencies = [
      'autolink',
      'embedbase',
      'notificationaggregator',
      'notification',
      'fakeobjects',
      'link',
    ];

    if ($this->needsTextMatchDependency()) {
      $dependencies[] = 'textmatch';
    }

    if ($embed_plugin = $settings['plugins']['autoembed']['status']) {
      $dependencies[] = $embed_plugin;
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

  /**
   * {@inheritdoc}
   */
  public function isEnabled(Editor $editor) {
    $settings = $editor->getSettings();

    return (isset($settings['plugins']['autoembed']['status']) && (bool) $settings['plugins']['autoembed']['status']);
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state, Editor $editor) {
    $settings = $editor->getSettings();

    $form['status'] = [
      '#type' => 'radios',
      '#title' => $this->t('Enable auto embed'),
      '#options' => [
        '' => $this->t('Disabled'),
        'embed' => $this->t('Media Embed'),
        'embedsemantic' => $this->t('Semantic Media Embed'),
      ],
      '#default_value' => !empty($settings['plugins']['autoembed']['status']) ? $settings['plugins']['autoembed']['status'] : '',
      '#description' => $this->t('When enabled to a Media embed plugin, media resource URLs pasted into the editing area are turned into an embed resource using the selected plugin.'),
    ];

    return $form;
  }

}
