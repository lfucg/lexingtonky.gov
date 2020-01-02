<?php

namespace Drupal\embed\Entity;

use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\embed\EmbedButtonInterface;

/**
 * Defines the EmbedButton entity.
 *
 * @ConfigEntityType(
 *   id = "embed_button",
 *   label = @Translation("Embed button"),
 *   handlers = {
 *     "form" = {
 *       "add" = "Drupal\embed\Form\EmbedButtonForm",
 *       "edit" = "Drupal\embed\Form\EmbedButtonForm",
 *       "delete" = "Drupal\Core\Entity\EntityDeleteForm",
 *     },
 *     "list_builder" = "Drupal\embed\EmbedButtonListBuilder",
 *   },
 *   admin_permission = "administer embed buttons",
 *   config_prefix = "button",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *   },
 *   links = {
 *     "edit-form" = "/admin/config/content/embed/button/manage/{embed_button}",
 *     "delete-form" = "/admin/config/content/embed/button/manage/{embed_button}/delete",
 *     "collection" = "/admin/config/content/embed",
 *   },
 *   config_export = {
 *     "label",
 *     "id",
 *     "type_id",
 *     "type_settings",
 *     "icon",
 *     "icon_uuid",
 *   }
 * )
 */
class EmbedButton extends ConfigEntityBase implements EmbedButtonInterface {

  use StringTranslationTrait;

  /**
   * The EmbedButton ID.
   *
   * @var string
   */
  public $id;

  /**
   * Label of EmbedButton.
   *
   * @var string
   */
  public $label;

  /**
   * The embed type plugin ID.
   *
   * @var string
   */
  public $type_id;

  /**
   * Embed type settings.
   *
   * An array of key/value pairs.
   *
   * @var array
   */
  public $type_settings = [];

  /**
   * An array of data about the encoded button image.
   *
   * @var array
   */
  public $icon = [];

  /**
   * {@inheritdoc}
   */
  public function getTypeId() {
    return $this->type_id;
  }

  /**
   * {@inheritdoc}
   */
  public function getTypeLabel() {
    if ($definition = $this->embedTypeManager()->getDefinition($this->getTypeId(), FALSE)) {
      return $definition['label'];
    }
    return $this->t('Unknown');
  }

  /**
   * {@inheritdoc}
   */
  public function getTypePlugin() {
    if ($plugin_id = $this->getTypeId()) {
      return $this->embedTypeManager()->createInstance($plugin_id, $this->getTypeSettings());
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getIconFile() {
    @trigger_error(__METHOD__ . ' is deprecated in Embed 1.2 and will be removed before 1.3.', E_USER_DEPRECATED);
    if (!empty($this->icon_uuid)) {
      $files = $this->entityTypeManager()->getStorage('file')->loadByProperties(['uuid' => $this->icon_uuid]);
      return reset($files);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getIconUrl() {
    if (!empty($this->icon)) {
      $uri = $this->icon['uri'];
      if (!is_file($uri) && !UrlHelper::isExternal($uri)) {
        static::convertEncodedDataToImage($this->icon);
      }
      $uri = file_create_url($uri);
    }
    else {
      $uri = $this->getTypePlugin()->getDefaultIconUrl();
    }

    return file_url_transform_relative($uri);
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    parent::calculateDependencies();

    // Gather the dependencies of the embed type plugin.
    if ($plugin = $this->getTypePlugin()) {
      $this->calculatePluginDependencies($plugin);
      return $this->dependencies;
    }
    return NULL;
  }

  /**
   * Gets the embed type plugin manager.
   *
   * @return \Drupal\embed\EmbedType\EmbedTypeManager
   *   The embed type plugin manager.
   */
  protected function embedTypeManager() {
    return \Drupal::service('plugin.manager.embed.type');
  }

  /**
   * {@inheritdoc}
   */
  public function getTypeSetting($key, $default = NULL) {
    if (isset($this->type_settings[$key])) {
      return $this->type_settings[$key];
    }
    return $default;
  }

  /**
   * {@inheritdoc}
   */
  public function getTypeSettings() {
    return $this->type_settings;
  }

  /**
   * {@inheritdoc}
   */
  public static function convertImageToEncodedData($uri) {
    return [
      'data' => base64_encode(file_get_contents($uri)),
      'uri' => $uri,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public static function convertEncodedDataToImage(array $data) {
    if (!is_file($data['uri'])) {
      $directory = dirname($data['uri']);
      /** @var \Drupal\Core\File\FileSystemInterface $filesystem */
      $fileSystem = \Drupal::service('file_system');
      $fileSystem->prepareDirectory($directory, FileSystemInterface::CREATE_DIRECTORY | FileSystemInterface::MODIFY_PERMISSIONS);
      $fileSystem->saveData(base64_decode($data['data']), $data['uri'], FileSystemInterface::EXISTS_REPLACE);
    }
    return $data['uri'];
  }

}
