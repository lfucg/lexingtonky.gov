<?php

namespace Drupal\ckeditor_media_embed;

/**
 * Defines an interface for processing and embedding embeddable content.
 */
interface EmbedInterface {

  /**
   * Retrieve the link to the configuration page for the settings.
   */
  public function getSettingsLink();

  /**
   * Sets specified provider url as the provider URL.
   */
  public function setEmbedProvider($provider);

  /**
   * Retrieve the Embed object as provided by the embed provider.
   *
   * @param string $url
   *   The url to the media to request an embed object for.
   *
   * @return object
   *   The decoded json object retrieved from the provided for the specified
   *   url.
   */
  public function getEmbedObject($url);

  /**
   * Replace all oembed tags with the embed html based ona provider resource.
   *
   * @param string $text
   *   The HTML string to replace <oembed> tags.
   *
   * @return string
   *   The HTML with all the <oembed> tags replaced with their embed html.
   */
  public function processEmbeds($text);

}
