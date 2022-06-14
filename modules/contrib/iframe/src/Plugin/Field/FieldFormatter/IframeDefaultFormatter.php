<?php

namespace Drupal\iframe\Plugin\Field\FieldFormatter;

use Drupal\Component\Render\HtmlEscapedText;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Url;
use Drupal\Core\Template\Attribute;

/**
 * Class IframeDefaultFormatter.
 *
 * @FieldFormatter(
 *  id = "iframe_default",
 *  module = "iframe",
 *  label = @Translation("Title, over iframe (default)"),
 *  field_types = {"iframe"}
 * )
 */
class IframeDefaultFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'url' => '',
      'title' => '',
      'headerlevel' => '3',
      'width' => '',
      'height' => '',
      'class' => '',
      'frameborder' => '0',
      'scrolling' => '',
      'transparency' => '0',
      'tokensupport' => '0',
      'allowfullscreen' => '0',
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];
    // settings from type
    $settings = $this->getSettings();
    // field_settings on concrete field
    $field_settings = $this->getFieldSettings();
    //\iframe_debug(3, __METHOD__, $settings);
    \iframe_debug(3, __METHOD__, $field_settings);
    \iframe_debug(3, __METHOD__, $items->getValue());
    $allow_attributes = [ 'url', 'width', 'height', 'title' ];
    foreach ($items as $delta => $item) {
      if (empty($item->url)) {
        continue;
      }
      if (!isset($item->title)) {
        $item->title = '';
      }
      foreach($field_settings as $field_key => $field_val) {
        if (in_array($field_key, $allow_attributes)) {
          continue;
        }
        $item->{$field_key} = $field_val;
      }
      $elements[$delta] = self::iframeIframe($item->title, $item->url, $item);
      // Tokens can be dynamic, so its not cacheable.
      if (isset($settings['tokensupport']) && $settings['tokensupport']) {
        $elements[$delta]['cache'] = ['max-age' => 0];
      }
    }
    return $elements;
  }

  /**
   * Like central function form the iframe code.
   */
  public static function iframeIframe($text, $path, $item) {
    // \iframe_debug(0, __METHOD__, $item->toArray());
    $options = [];
    $options['width'] = empty($item->width) ? '100%' : $item->width;
    $options['height'] = empty($item->height) ? '701' : $item->height;
    // Collect all allow policies.
    $allow = [];
    // Collect styles, but leave it overwritable.
    $style = '';
    $itemName = $item->getFieldDefinition()->getName();
    $itemParentId = $item->getParent()->getParent()->getEntity()->ID();

    if (!empty($item->frameborder) && $item->frameborder > 0) {
      $style .= '/*frameborder*/ border-width:2px;';
    }
    else {
      $style .= '/*frameborder*/ border-width:0;';
    }
    if (!empty($item->scrolling)) {
      if ($item->scrolling == 'yes') {
        $style .= '/*scrolling*/ overflow:scroll;';
      }
      elseif ($item->scrolling == 'no') {
        $style .= '/*scrolling*/ overflow:hidden;';
      }
      else {
        // Default: auto.
        $style .= '/*scrolling*/ overflow:auto;';
      }
    }
    if (!empty($item->transparency) && $item->transparency > 0) {
      $style .= '/*transparency*/ background-color:transparent;';
    }

    $htmlid = 'iframe-' . $itemName . '-' . $itemParentId;
    if (property_exists($item, 'htmlid') && $item->htmlid !== null && !empty($item->htmlid)) {
      $htmlid = $item->htmlid;
    }
    $htmlid = preg_replace('#[^A-Za-z0-9\-\_]+#', '-', $htmlid);
    $options['id'] = $options['name'] = $htmlid;

    // Append active class.
    $options['class'] = empty($item->class) ? '' : $item->class;

    // Remove all HTML and PHP tags from a tooltip.
    // For best performance, we act only
    // if a quick strpos() pre-check gave a suspicion
    // (because strip_tags() is expensive).
    $options['title'] = empty($item->title) ? '' : $item->title;
    if (!empty($options['title']) && strpos($options['title'], '<') !== FALSE) {
      $options['title'] = strip_tags($options['title']);
    }
    $headerlevel = 3; #default h3
    if (isset($item->headerlevel) && $item->headerlevel >= 1 && $item->headerlevel <= 6) {
      $headerlevel = (int)$item->headerlevel;
    }

    // Policy attribute.
    $allow[] = 'accelerometer';
    $allow[] = 'autoplay';
    $allow[] = 'camera';
    $allow[] = 'encrypted-media';
    $allow[] = 'geolocation';
    $allow[] = 'gyroscope';
    $allow[] = 'microphone';
    $allow[] = 'payment';
    $allow[] = 'picture-in-picture';
    $options['allow'] = implode(';', $allow);
    if (!empty($item->allowfullscreen) && $item->allowfullscreen) {
      $options['allowfullscreen'] = 'allowfullscreen';
    }

    if (\Drupal::moduleHandler()->moduleExists('token')) {
      // Token Support for field "url" and "title".
      $tokensupport = $item->getTokenSupport();
      $tokencontext = ['user' => \Drupal::currentUser()];
      if (isset($GLOBALS['node'])) {
        $tokencontext['node'] = $GLOBALS['node'];
      }
      if ($tokensupport > 0) {
        $text = \Drupal::token()->replace($text, $tokencontext);
      }
      if ($tokensupport > 1) {
        $path = \Drupal::token()->replace($path, $tokencontext);
      }
    }

    $options_link = [];
    $options_link['attributes'] = [];
    $options_link['attributes']['title'] = $options['title'];
    try {
      $srcuri = Url::fromUri($path, $options_link);
      $src = $srcuri->toString();
      $options['src'] = $src;
      $drupal_attributes = new Attribute($options);
      $element = [
        '#theme' => 'iframe',
        '#src' => $src,
        '#attributes' => $drupal_attributes,
        '#text' => (isset($options['html']) && $options['html'] ? $text : new HtmlEscapedText($text)),
        '#style' => 'iframe#' . $htmlid . ' {' . $style . '}',
        '#headerlevel' => $headerlevel,
      ];
      return $element;
    } catch (\Exception $excep) {
      // \iframe_debug(0, __METHOD__, $excep);
      watchdog_exception(__METHOD__, $excep);
      return [];
    }
  }
}
