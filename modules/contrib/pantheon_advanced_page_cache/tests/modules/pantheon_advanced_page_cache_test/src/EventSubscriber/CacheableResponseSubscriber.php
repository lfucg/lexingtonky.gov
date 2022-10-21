<?php

namespace Drupal\pantheon_advanced_page_cache_test\EventSubscriber;

use Drupal\Core\Cache\CacheableResponseInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Adds Surrogate-Key header to cacheable master responses.
 */
class CacheableResponseSubscriber implements EventSubscriberInterface {

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

      // This is a contrived example of how custom code can be used
      // to limit a giant list of tags.
      // In this case, automated Behat tests generate nodes
      // tagged in 100s of taxonomy terms each. Then when
      // those nodes are rendered on a view like frontpage
      // they result in too many total surrogate-keys being set.
      if (in_array("config:views.view.frontpage", $tags)) {
        $new_tags = [];
        foreach ($tags as $tag) {
          if (strpos($tag, "taxonomy_term:") === FALSE) {
            $new_tags[] = $tag;
          }
        }
        $response->getCacheableMetadata()->setCacheTags($new_tags);
      }

    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[KernelEvents::RESPONSE][] = ['onRespond', 100];
    return $events;
  }

}
