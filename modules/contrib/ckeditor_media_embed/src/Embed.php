<?php

namespace Drupal\ckeditor_media_embed;

use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Link;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Path\CurrentPathStack;
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
   * @var \Drupal\Core\Utility\UnroutedUrlAssemblerInterface
   */
  protected $urlAssembler;

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * The messenger.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * The config factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactory
   */
  protected $configFactory;

  /**
   * The current path service.
   *
   * @var \Drupal\Core\Path\CurrentPathStack
   */
  protected $currentPath;

  /**
   * Constructs an Embed object.
   *
   * @param \GuzzleHttp\ClientInterface $http_client
   *   The http client used to do retrieval of embed codes.
   * @param \Drupal\Core\Utility\UnroutedUrlAssemblerInterface $url_assembler
   *   The url assembler used to create url from a parsed url.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger.
   * @param \Drupal\Core\Config\ConfigFactory $config_factory
   *   The config factory service.
   * @param \Drupal\Core\Path\CurrentPathStack $current_path
   *   The current path service.
   */
  public function __construct(ClientInterface $http_client, UnroutedUrlAssemblerInterface $url_assembler, RequestStack $request_stack, MessengerInterface $messenger, ConfigFactory $config_factory, CurrentPathStack $current_path) {
    $this->httpClient = $http_client;
    $this->urlAssembler = $url_assembler;
    $this->requestStack = $request_stack;
    $this->configFactory = $config_factory;
    $this->messenger = $messenger;
    $this->currentPath = $current_path;

    $embed_provider = $this->configFactory->get('ckeditor_media_embed.settings')->get('embed_provider');
    $this->setEmbedProvider($embed_provider);
  }

  /**
   * {@inheritdoc}
   */
  public function setEmbedProvider($provider) {
    $provider_parsed = UrlHelper::parse($provider);

    $provider_parsed['query'] = array_filter($provider_parsed['query'], function ($value) {
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
      $this->messenger->addWarning($this->t('Unable to retrieve @url at this time, please check again later.', ['@url' => $url]));
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
    $url = URL::fromRoute('ckeditor_media_embed.ckeditor_media_embed_settings_form', ['destination' => $this->currentPath->getPath()]);
    return Markup::create(Link::fromTextAndUrl($this->t('CKEditor Media Embed plugin settings page'), $url)->toString());
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
    $embed_body_node = HTML::load($embed->html)->getElementsByTagName('body')->item(0);
    foreach ($embed_body_node->childNodes as $child) {
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
   *   The safe HTML class string to apply the new embed html node.
   */
  protected function getClass($embed) {
    return 'embed-media ' . HTML::getClass("embed-media--$embed->type-$embed->provider_name");
  }

}
