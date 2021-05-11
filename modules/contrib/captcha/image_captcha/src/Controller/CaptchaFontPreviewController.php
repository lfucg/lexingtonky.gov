<?php

namespace Drupal\image_captcha\Controller;

use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\image_captcha\StreamedResponse\CaptchaFontPreviewStreamedResponse;
use Drupal\Core\PageCache\ResponsePolicy\KillSwitch;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Controller which generates the image from defined settings.
 */
class CaptchaFontPreviewController implements ContainerInjectionInterface {

  /**
   * Image Captcha config storage.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $config;

  /**
   * Kill Switch for page caching.
   *
   * @var \Drupal\Core\PageCache\ResponsePolicy\KillSwitch
   */
  protected $killSwitch;

  /**
   * {@inheritdoc}
   */
  public function __construct(ImmutableConfig $config, KillSwitch $kill_switch) {
    $this->config = $config;
    $this->killSwitch = $kill_switch;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory')->get('image_captcha.settings'),
      $container->get('page_cache_kill_switch')
    );
  }

  /**
   * Main method that throw ImageResponse object to generate image.
   *
   * @return \Drupal\image_captcha\StreamedResponse\CaptchaFontPreviewStreamedResponse
   *   Make a CaptchaImageResponse with the correct configuration and return it.
   */
  public function getFont($token) {
    $this->killSwitch->trigger();
    return new CaptchaFontPreviewStreamedResponse($this->config, $token);
  }

}
