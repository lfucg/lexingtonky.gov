<?php

namespace Drupal\image_captcha\StreamedResponse;

use Drupal\Core\Config\ImmutableConfig;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * A Controller to preview the captcha font on the settings page.
 */
class CaptchaFontPreviewStreamedResponse extends StreamedResponse {

  /**
   * Config service.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $config;

  /**
   * Token font selector.
   *
   * @string
   */
  protected $token;

  /**
   * {@inheritdoc}
   */
  public function __construct(ImmutableConfig $config, $token, $callback = NULL, $status = 200, $headers = []) {
    parent::__construct(NULL, $status, $headers);

    $this->config = $config;
    $this->token = $token;
  }

  /**
   * {@inheritdoc}
   */
  public function sendContent() {
    // Get the font from the given font token.
    if ($this->token == 'BUILTIN') {
      $font = 'BUILTIN';
    }
    else {
      // Get the mapping of font tokens to font file objects.
      $fonts = $this->config->get('image_captcha_fonts_preview_map_cache');
      if (!isset($fonts[$this->token])) {
        return 'bad token';
      }
      // Get the font path.
      $font = $fonts[$this->token]['uri'];
      // Some sanity checks if the given font is valid.
      if (!is_file($font) || !is_readable($font)) {
        return 'bad font';
      }
    }

    // Settings of the font preview.
    $width = 120;
    $text = 'AaBbCc123';
    $font_size = 14;
    $height = 2 * $font_size;

    // Allocate image resource.
    $image = imagecreatetruecolor($width, $height);
    if (!$image) {
      return NULL;
    }
    // White background and black foreground.
    $background_color = imagecolorallocate($image, 255, 255, 255);
    $color = imagecolorallocate($image, 0, 0, 0);
    imagefilledrectangle($image, 0, 0, $width, $height, $background_color);

    // Draw preview text.
    if ($font == 'BUILTIN') {
      imagestring($image, 5, 1, .5 * $height - 10, $text, $color);
    }
    else {
      imagettftext($image, $font_size, 0, 1, 1.5 * $font_size, $color, realpath($font), $text);
    }
    // Set content type.
    $this->headers->set('Content-Type', 'image/png');
    // Dump image data to client.
    imagepng($image);
    // Release image memory.
    imagedestroy($image);
  }

}
