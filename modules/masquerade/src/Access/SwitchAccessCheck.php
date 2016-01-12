<?php
/**
 * @file
 * Contains \Drupal\masquerade\Access\SwitchAccessCheck.
 */

namespace Drupal\masquerade\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\masquerade\Masquerade;
use Drupal\user\Entity\Role;

/**
 * Checks access for any masquerade permissions.
 */
class SwitchAccessCheck implements AccessInterface {

  /**
   * The masquerade service.
   *
   * @var \Drupal\masquerade\Masquerade
   */
  protected $masquerade;

  /**
   * Constructs a new UnmasqueradeAccessCheck object.
   *
   * @param \Drupal\masquerade\Masquerade $masquerade
   *   The masquerade service.
   */
  public function __construct(Masquerade $masquerade) {
    $this->masquerade = $masquerade;
  }

  /**
   * Check to see if user has any permissions to masquerade.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   Run access checks for this account.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The access result.
   */
  public function access(AccountInterface $account) {
    // Uid 1 may masquerade as anyone.
    if ($account->id() == 1) {
      return AccessResult::allowed()->cachePerUser();
    }
    $permissions = $this->masquerade->getPermissions();
    return AccessResult::allowedIfHasPermissions($account, $permissions, 'OR');
  }

}
