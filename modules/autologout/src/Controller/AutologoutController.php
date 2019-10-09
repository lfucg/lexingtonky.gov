<?php

namespace Drupal\autologout\Controller;

use Drupal\autologout\AutologoutManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Ajax;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Controller\ControllerBase;

/**
 * Returns responses for autologout module routes.
 */
class AutologoutController extends ControllerBase {

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
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('autologout.manager')
    );
  }

  /**
   * AJAX callback that performs the actual logout and redirects the user.
   */
  public function ahahLogout() {
    $this->autoLogoutManager->logout();
    $response = new AjaxResponse();
    $response->setStatusCode(200);
    return $response;
  }

  /**
   * Ajax callback to reset the last access session variable.
   */
  public function ahahSetLast() {
    $_SESSION['autologout_last'] = REQUEST_TIME;

    // Reset the timer.
    $response = new AjaxResponse();
    $markup = $this->autoLogoutManager->createTimer();
    $response->addCommand(new Ajax\ReplaceCommand('#timer', $markup));

    return $response;
  }

  /**
   * AJAX callback that returns the time remaining for this user is logged out.
   */
  public function ahahGetRemainingTime() {
    $time_remaining_ms = $this->autoLogoutManager->getRemainingTime() * 1000;

    // Reset the timer.
    $response = new AjaxResponse();
    $markup = $this->autoLogoutManager->createTimer();

    $response->addCommand(new Ajax\ReplaceCommand('#timer', $markup));
    $response->addCommand(new Ajax\SettingsCommand(['time' => $time_remaining_ms]));

    return $response;
  }

}
