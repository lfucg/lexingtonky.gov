<?php

namespace Drupal\autologout\EventSubscriber;

use Drupal\autologout\AutologoutManagerInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Defines autologout Subscriber.
 */
class AutologoutSubscriber implements EventSubscriberInterface {

  /**
   * The autologout manager service.
   *
   * @var \Drupal\autologout\AutologoutManagerInterface
   */
  protected $autoLogoutManager;

  /**
   * Constructs an AutologoutSubscriber object.
   *
   * @param \Drupal\autologout\AutologoutManagerInterface $autologout
   *   The autologout manager service.
   */
  public function __construct(AutologoutManagerInterface $autologout) {
    $this->autoLogoutManager = $autologout;
  }

  /**
   * Check for autologout JS.
   *
   * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
   *   The request event.
   */
  public function onRequest(GetResponseEvent $event) {
    $autologout_manager = \Drupal::service('autologout.manager');

    $uid = \Drupal::currentUser()->id();

    if ($uid == 0) {
      if (!empty($_GET['autologout_timeout']) && $_GET['autologout_timeout'] == 1 && empty($_POST)) {
        $autologout_manager->inactivityMessage();
      }
      return;
    }

    if ($this->autoLogoutManager->preventJs()) {
      return;
    }

    $now = REQUEST_TIME;
    // Check if anything wants to be refresh only. This URL would include the
    // javascript but will keep the login alive whilst that page is opened.
    $refresh_only = $autologout_manager->refreshOnly();
    $settings = \Drupal::config('autologout.settings');
    $timeout = $autologout_manager->getUserTimeout();
    $timeout_padding = $settings->get('padding');

    // We need a backup plan if JS is disabled.
    if (!$refresh_only && isset($_SESSION['autologout_last'])) {
      // If time since last access is > timeout + padding, log them out.
      $diff = $now - $_SESSION['autologout_last'];
      if ($diff >= ($timeout + (int) $timeout_padding)) {
        $autologout_manager->logout();
        // User has changed so force Drupal to remake decisions based on user.
        global $theme, $theme_key;
        drupal_static_reset();
        $theme = NULL;
        $theme_key = NULL;
        \Drupal::theme()->getActiveTheme();
        $autologout_manager->inactivityMessage();
      }
      else {
        $_SESSION['autologout_last'] = $now;
      }
    }
    else {
      $_SESSION['autologout_last'] = $now;
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[KernelEvents::REQUEST][] = ['onRequest', 100];
    return $events;
  }

}
