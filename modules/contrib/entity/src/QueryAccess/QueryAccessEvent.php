<?php

namespace Drupal\entity\QueryAccess;

use Drupal\Core\Session\AccountInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Defines the query access event.
 *
 * Allows modules to modify access conditions before they're applied to a query.
 *
 * The event ID is dynamic: entity.query_access.$entity_type_id
 */
class QueryAccessEvent extends Event {

  /**
   * The conditions.
   *
   * @var \Drupal\entity\QueryAccess\ConditionGroup
   */
  protected $conditions;

  /**
   * The operation.
   *
   * @var string
   */
  protected $operation;

  /**
   * The user for which to restrict access.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $account;

  /**
   * Constructs a new QueryAccessEvent.
   *
   * @param \Drupal\entity\QueryAccess\ConditionGroup $conditions
   *   The conditions.
   * @param string $operation
   *   The operation. Usually one of "view", "update", "duplicate", or "delete".
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user for which to restrict access.
   */
  public function __construct(ConditionGroup $conditions, $operation, AccountInterface $account) {
    $this->conditions = $conditions;
    $this->operation = $operation;
    $this->account = $account;
  }

  /**
   * Gets the conditions.
   *
   * If $conditions->isAlwaysFalse() is TRUE, the user doesn't have access to
   * any entities, and the query is expected to return no results.
   * This can be reversed by calling $conditions->alwaysFalse(FALSE).
   *
   * If $conditions->isAlwaysFalse() is FALSE, and the condition group is
   * empty (count is 0), the user has full access, and the query doesn't
   * need to be restricted.
   *
   * @return \Drupal\entity\QueryAccess\ConditionGroup
   *   The conditions.
   */
  public function getConditions() {
    return $this->conditions;
  }

  /**
   * Gets the operation.
   *
   * @return string
   *   The operation. Usually one of "view", "update" or "delete".
   */
  public function getOperation() {
    return $this->operation;
  }

  /**
   * Gets the user for which to restrict access.
   *
   * @return \Drupal\Core\Session\AccountInterface
   *   The user.
   */
  public function getAccount() {
    return $this->account;
  }

}
