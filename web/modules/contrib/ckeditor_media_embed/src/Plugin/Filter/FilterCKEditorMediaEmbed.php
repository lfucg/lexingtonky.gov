<?php

namespace Drupal\ckeditor_media_embed\Plugin\Filter;

use Drupal\ckeditor_media_embed\EmbedInterface;

use Drupal\Core\Form\FormStateInterface;
use Drupal\filter\FilterProcessResult;
use Drupal\filter\Plugin\FilterBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a filter to convert URLs into links.
 *
 * @Filter(
 *   id = "filter_ckeditor_media_embed",
 *   title = @Translation("Convert Oembed tags to media embeds"),
 *   type = Drupal\filter\Plugin\FilterInterface::TYPE_TRANSFORM_REVERSIBLE
 * )
 */
class FilterCKEditorMediaEmbed extends FilterBase implements ContainerFactoryPluginInterface {

  /**
   * The Embed object used to convert <oembed> tags to embed html.
   *
   * @var Drupal\ckeditor_media_embed\Embed
   */
  protected $ckeditorMediaEmbed;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EmbedInterface $ckeditor_media_embed) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->ckeditorMediaEmbed = $ckeditor_media_embed;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('ckeditor_media_embed')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function process($text, $langcode) {
    $result = new FilterProcessResult($text);

    if (strpos($text, '<oembed') !== FALSE) {
      $result->setProcessedText($this->ckeditorMediaEmbed->processEmbeds($text));
    }

    return $result;
  }

  /**
   * {@inheritdoc}
   */
  public function tips($long = FALSE) {
    return $this->t('Oembed <code>&lt;oembed&gt;URL&lt;/oembed&gt;</code> tags are converted to the media embed HTML.');
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->t('The provider specified as the @link will be used.', ['@link' => $this->ckeditorMediaEmbed->getSettingsLink()]);
  }

}
