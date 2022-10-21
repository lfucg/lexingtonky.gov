<?php

namespace Drupal\pantheon_advanced_page_cache\EventSubscriber;

use Drupal\Core\Cache\CacheableResponseInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Psr\Log\LoggerInterface;
use Drupal\Core\Logger\RfcLogLevel;
use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Adds Surrogate-Key header to cacheable master responses.
 */
class CacheableResponseSubscriber implements EventSubscriberInterface {

  /**
   * The logger instance.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * Configuration Factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Constructs a new DefaultExceptionHtmlSubscriber.
   *
   * @param \Psr\Log\LoggerInterface $logger
   *   The logger service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Configuration for this module.
   */
  public function __construct(LoggerInterface $logger, ConfigFactoryInterface $config_factory = NULL) {
    if (!$config_factory instanceof ConfigFactoryInterface) {
      @trigger_error('Not passing the config factory service as the second parameter to ' . __METHOD__ . ' is deprecated in pantheon_advanced_page_cache:8.x-1.2 and will throw a type error in pantheon_advanced_page_cache:8.x-2.0. Pass an instance of \\Drupal\\Core\\Config\\ConfigFactoryInterface. See https://www.drupal.org/node/2944229', E_USER_DEPRECATED);
      $config_factory = \Drupal::service('config.factory');
    }
    $this->logger = $logger;
    $this->configFactory = $config_factory;
  }

  /**
   * Returns whether entity_list tags should be overridden.
   *
   * Overriding these tags was the initial behavior of the 1.0 version of this
   * module. That is no longer recommended.
   */
  public function getOverrideListTagsSetting() {
    $config = $this->configFactory->get('pantheon_advanced_page_cache.settings');
    // Only return FALSE if this config value is really set to false.
    // A null value should return TRUE for backwards compatibility.
    if ($config->get('override_list_tags') === FALSE) {
      return FALSE;
    }
    return TRUE;
  }

  /**
   * Adds Surrogate-Key header to cacheable master responses.
   *
   * @param \Symfony\Component\HttpKernel\Event\FilterResponseEvent $event
   *   The event to process.
   */
  public function onRespond(FilterResponseEvent $event) {
    if (!$event->isMasterRequest()) {
      return;
    }

    $response = $event->getResponse();

    if ($response instanceof CacheableResponseInterface) {
      $tags = $response->getCacheableMetadata()->getCacheTags();

      // Rename all _list cache tags to _emit_list to avoid clearing list cache
      // tags by default.
      if ($this->getOverrideListTagsSetting()) {
        foreach ($tags as $key => $tag) {
          $tags[$key] = str_replace('_list', '_emit_list', $tag);
        }
      }

      $tags_string = implode(' ', $tags);
      if (25000 < strlen($tags_string)) {
        $tags_string = substr($tags_string, 0, 25000);
        // The string might have cut of in the middle of a tag.
        // So now find the the last occurence of a space and cut to that length.
        $tags_string = substr($tags_string, 0, strrpos($tags_string, ' '));
        $this->logger->log(RfcLogLevel::WARNING, 'More cache tags were present than could be passed in the Surrogate-Key HTTP Header due to length constraints. To avoid a 502 error the list of surrogate keys was trimmed to a maximum length of 25,000 bytes. Since keys beyond the 25,000 maximum were removed this page will not be cleared from the cache when any of the removed keys are cleared (usually by entity save operations) as they have been stripped from the surrogate key header. See https://www.drupal.org/project/pantheon_advanced_page_cache/issues/2973861 for more information about how you can filter out redundant or unnecessary cache metadata.');
      }
      $response->headers->set('Surrogate-Key', $tags_string);
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[KernelEvents::RESPONSE][] = ['onRespond'];
    return $events;
  }

}
