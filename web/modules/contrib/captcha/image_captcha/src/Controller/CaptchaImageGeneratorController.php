<?php

namespace Drupal\image_captcha\Controller;

use Drupal\Core\Config\Config;
use Drupal\Core\Database\Connection;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Psr\Log\LoggerInterface;
use Drupal\Core\PageCache\ResponsePolicy\KillSwitch;
use Drupal\image_captcha\Response\CaptchaImageResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Controller which generates the image from defined settings.
 */
class CaptchaImageGeneratorController implements ContainerInjectionInterface {

  /**
   * Connection container.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * Image Captcha config storage.
   *
   * @var \Drupal\Core\Config\Config
   */
  protected $config;

  /**
   * File System Service.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;

  /**
   * Watchdog logger channel for captcha.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * Kill Switch for page caching.
   *
   * @var \Drupal\Core\PageCache\ResponsePolicy\KillSwitch
   */
  protected $killSwitch;

  /**
   * {@inheritdoc}
   */
  public function __construct(Config $config, LoggerInterface $logger, KillSwitch $kill_switch, Connection $connection, FileSystemInterface $file_system) {
    $this->config = $config;
    $this->logger = $logger;
    $this->killSwitch = $kill_switch;
    $this->connection = $connection;
    $this->fileSystem = $file_system;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory')->get('image_captcha.settings'),
      $container->get('logger.factory')->get('captcha'),
      $container->get('page_cache_kill_switch'),
      $container->get('database'),
      $container->get('file_system')
    );
  }

  /**
   * Main method that throw ImageResponse object to generate image.
   *
   * @return \Drupal\image_captcha\Response\CaptchaImageResponse
   *   Make a CaptchaImageResponse with the correct configuration and return it.
   */
  public function image() {
    $this->killSwitch->trigger();
    return new CaptchaImageResponse($this->config, $this->logger, $this->connection, $this->fileSystem);
  }

}
