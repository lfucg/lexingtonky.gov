<?php

namespace Drupal\ckeditor_media_embed;

use Drupal\Core\Render\Markup;
use Drupal\Core\Url;
use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Utility\UnroutedUrlAssemblerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\TransferException;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * The default CKEditor Media Embed class.
 */
class Embed implements EmbedInterface {
  use StringTranslationTrait;

  /**
   * The HTTP client to fetch the embed code with.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected $httpClient;

  /**
   * The unrouted URL assembler service.
   *
   * @var Drupal\Core\Utility\UnroutedUrlAssemblerInterface
   */
  protected $urlAssembler;

  /**
   * The request stack.
   *
   * @var Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * Constructs an Embed object.
   *
   * @param ClientInterface $httpClient
   *   The http client used to do retrieval of embed codes.
   * @param UnroutedUrlAssemblerInterface $urlAssembler
   *   The url assembler used to create url from a parsed url.
   * @param RequestStack $requestStack
   *   The request stack.
   */
  public function __construct(ClientInterface $httpClient, UnroutedUrlAssemblerInterface $urlAssembler, RequestStack $requestStack) {
    $this->httpClient = $httpClient;
    $this->urlAssembler = $urlAssembler;
    $this->requestStack = $requestStack;
    $this->setEmbedProvider(\Drupal::config('ckeditor_media_embed.settings')->get('embed_provider'));
  }

  /**
   * {@inheritdoc}
   */
  public function setEmbedProvider($provider) {
    $provider_parsed = UrlHelper::parse($provider);

    $provider_parsed['query'] = array_filter($provider_parsed['query'], function($value) {
      return ($value !== '{callback}');
    });

    $provider_parsed['absolute'] = TRUE;
    $this->embed_provider = $this->urlAssembler->assemble($provider_parsed['path'], $provider_parsed);
  }

  /**
   * {@inheritdoc}
   */
  public function getEmbedObject($url) {
    $embed = NULL;

    try {
      $response = $this->httpClient->get($this->getEmbedProviderURL($url), ['headers' => ['content-type' => 'application/json']]);
      $embed = json_decode($response->getBody());
    }
    catch (TransferException $e) {
      drupal_set_message(t('Unable to retrieve @url at this time, please check again later.', ['@url' => $url]), 'warning');
      watchdog_exception('ckeditor_media_embed', $e);
    }

    return $embed;
  }

  /**
   * Inject the media url into the provider url.
   *
   * @return string
   *   The provider url with the media url injected.
   */
  // @codingStandardsIgnoreLine
  protected function getEmbedProviderURL($url) {
    $provider = $this->embed_provider;

    if (strpos($provider, '//') === 0) {
      $provider = $this->requestStack->getCurrentRequest()->getScheme() . ':' . $provider;
    }

    return str_replace('%7Burl%7D', urlencode($url), $provider);
  }

  /**
   * {@inheritdoc}
   */
  public function processEmbeds($text) {
    $document = Html::load($text);
    $xpath = new \DOMXPath($document);

    foreach ($xpath->query('//oembed') as $node) {
      $embed = $this->getEmbedObject($node->nodeValue);

      if (!empty($embed) && !empty($embed->html)) {
        $this->swapEmbedHtml($node, $embed);
      }
    }

    return Html::serialize($document);
  }

  /**
   * {@inheritdoc}
   */
  public function getSettingsLink() {
    $url = URL::fromRoute('ckeditor_media_embed.ckeditor_media_embed_settings_form', array('destination' => \Drupal::service('path.current')->getPath()));
    return Markup::create(\Drupal::l($this->t('CKEditor Media Embed plugin settings page'), $url));
  }

  /**
   * Replace <oembed> tags with their respected embed HTML.
   *
   * @param \DOMNode $node
   *   The DOMNode object of the <oembed> tag.
   * @param object $embed
   *   The embed json decoded object as provided by Embed::getEmbedOjbect().
   *
   * @return $this
   */
  protected function swapEmbedHtml(\DOMNode &$node, $embed) {
    $embed_node = $node->ownerDocument->createElement('div');
    $embed_node->setAttribute('class', $this->getClass($embed));

    $child = NULL;
    $embed_document = HTML::load($embed->html);
    foreach ($embed_document->childNodes as $child) {
      if ($child = $node->ownerDocument->importNode($child, TRUE)) {
        $embed_node->appendChild($child);
      }
    }

    $node->parentNode->replaceChild($embed_node, $node);

    return $this;
  }

  /**
   * Retrieve the HTML class to apply to the new embed html node.
   *
   * @param object $embed
   *   The embed json decoded object as provided by Embed::getEmbedOjbect().
   *
   * @return string
   *    The safe HTML class string to apply the new embed html node.
   */
  protected function getClass($embed) {
    return 'embed-media ' . HTML::getClass("embed-media--$embed->type-$embed->provider_name");
  }

}
