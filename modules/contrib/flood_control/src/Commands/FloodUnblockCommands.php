<?php

namespace Drupal\flood_control\Commands;

use Drush\Commands\DrushCommands;
use Drupal\flood_control\FloodUnblockManager;

/**
 * Flood unblock. module's Drush 9 commands.
 */
class FloodUnblockCommands extends DrushCommands {

  /**
   * The FloodUnblockManager service.
   *
   * @var \Drupal\flood_control\FloodUnblockManager
   */
  private $manager;

  /**
   * FloodUnblockCommands constructor.
   *
   * @param \Drupal\flood_control\FloodUnblockManager $manager
   *   The FloodUnblockManager service.
   */
  public function __construct(FloodUnblockManager $manager) {
    $this->manager = $manager;
  }

  /**
   * Clears the floods based on IP.
   *
   * @param string $ip
   *   IP to clear.
   *
   * @command flood_unblock:ip
   * @usage flood_unblock:ip
   */
  public function unblockIp($ip = NULL) {
    $this->manager->floodUnblockClearEvent('user.failed_login_ip', $ip);

    $this->output()->writeln('Done');
  }

  /**
   * Clears the floods based on user.
   *
   * @param string $user
   *   User to clear...
   *
   * @command flood_unblock:user
   * @usage flood_unblock:user
   */
  public function unblockUser($user = NULL) {
    $this->manager->floodUnblockClearEvent('user.failed_login_user', $user);
    $this->manager->floodUnblockClearEvent('user.http_login', $user);
    $this->output()->writeln('Done');
  }

  /**
   * Clears all floods in the system.
   *
   * @command flood_unblock:all
   * @usage flood_unblock:all
   */
  public function unblockAll() {
    $this->manager->floodUnblockClearEvent('user.failed_login_ip', NULL);
    $this->manager->floodUnblockClearEvent('user.failed_login_user', NULL);
    $this->manager->floodUnblockClearEvent('user.http_login', NULL);
    $this->output()->writeln('Done');
  }

}
