<?php

namespace Drupal\ckeditor_media_embed\Plugin\Field\FieldFormatter;

use Drupal\ckeditor_media_embed\EmbedInterface;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the 'ckeditor_media_embed_link_formatter' formatter.
 *
 * @FieldFormatter(
 *   id = "ckeditor_media_embed_link_formatter",
 *   label = @Translation("Oembed element using CKEditor Media Embed provider"),
 *   field_types = {
 *     "link"
 *   }
 * )
 */
class CKEditorMediaEmbedFormatter extends FormatterBase implements ContainerFactoryPluginInterface {

  /**
   * The embed service.
   *
   * @var \Drupal\ckeditor_media_embed\EmbedInterfacee
   */
  protected $embed;

  /**
   * Constructs a CKEditorMediaEmbedFormatter object.
   *
   * @param string $plugin_id
   *   The plugin_id for the formatter.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The definition of the field to which the formatter is associated.
   * @param array $settings
   *   The formatter settings.
   * @param string $label
   *   The formatter label display setting.
   * @param string $view_mode
   *   The view mode.
   * @param array $third_party_settings
   *   Third party settings.
   * @param \Drupal\ckeditor_media_embed\EmbedInterface $embed
   *   The embed service.
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, $label, $view_mode, array $third_party_settings, EmbedInterface $embed) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings);

    $this->embed = $embed;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['label'],
      $configuration['view_mode'],
      $configuration['third_party_settings'],
      $container->get('ckeditor_media_embed')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $element = [];

    foreach ($items as $delta => $item) {
      $url = $item->getUrl();

      $output = $this->embed->getEmbedObject($url->toUriString());
      if (isset($output->html)) {
        $element[$delta] = [
          '#type' => 'inline_template',
          '#template' => '{{ content|raw }}',
          '#context' => [
            'content' => $output->html,
          ],
        ];
      }
      else {
        // If we didn't get an oembed response, just show the URL.
        $element[$delta] = [
          '#type' => 'link',
          '#url' => $url,
          '#title' => $url->toString(),
        ];
      }
    }

    return $element;
  }

}
