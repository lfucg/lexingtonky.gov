<?php

namespace Drupal\autologout\EventSubscriber;

use Drupal\autologout\AutologoutManagerInterface;
use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Theme\ThemeManager;
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
   * The user account service.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * The Config service.
   *
   * @var \Drupal\Core\Config\ConfigFactory
   */
  protected $config;

  /**
   * The theme manager service.
   *
   * @var \Drupal\Core\Theme\ThemeManager
   */
  protected $theme;

  /**
   * The Time Service.
   *
   * @var \Drupal\Component\Datetime\TimeInterface
   */
  protected $time;

  /**
   * Constructs an AutologoutSubscriber object.
   *
   * @param \Drupal\autologout\AutologoutManagerInterface $autologout
   *   The autologout manager service.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user account service.
   * @param \Drupal\Core\Config\ConfigFactory $config
   *   The Config service.
   * @param \Drupal\Core\Theme\ThemeManager $theme
   *   The theme manager service.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The time service.
   */
  public function __construct(AutologoutManagerInterface $autologout, AccountInterface $account, ConfigFactory $config, ThemeManager $theme, TimeInterface $time) {
    $this->autoLogoutManager = $autologout;
    $this->currentUser = $account;
    $this->config = $config;
    $this->theme = $theme;
    $this->time = $time;
  }

  /**
   * Check for autologout JS.
   *
   * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
   *   The request event.
   */
  public function onRequest(GetResponseEvent $event) {
    $autologout_manager = $this->autoLogoutManager;

    $uid = $this->currentUser->id();

    if ($uid == 0) {
      if (!empty($_GET['autologout_timeout']) && $_GET['autologout_timeout'] == 1 && empty($_POST)) {
        $autologout_manager->inactivityMessage();
      }
      return;
    }

    if ($this->autoLogoutManager->preventJs()) {
      return;
    }

    $now = $this->time->getRequestTime();
    // Check if anything wants to be refresh only. This URL would include the
    // javascript but will keep the login alive whilst that page is opened.
    $refresh_only = $autologout_manager->refreshOnly();
    $timeout = $autologout_manager->getUserTimeout();
    $timeout_padding = $this->config->get('autologout.settings')->get('padding');
    $is_altlogout = strpos($event->getRequest()->getRequestUri(), '/autologout_alt_logout') !== FALSE;

    // We need a backup plan if JS is disabled.
    if (!$is_altlogout && !$refresh_only && isset($_SESSION['autologout_last'])) {
      // If time since last access is > timeout + padding, log them out.
      $diff = $now - $_SESSION['autologout_last'];
      if ($diff >= ($timeout + (int) $timeout_padding)) {
        $autologout_manager->logout();
        // User has changed so force Drupal to remake decisions based on user.
        global $theme, $theme_key;
        drupal_static_reset();
        $theme = NULL;
        $theme_key = NULL;
        $this->theme->getActiveTheme();
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
