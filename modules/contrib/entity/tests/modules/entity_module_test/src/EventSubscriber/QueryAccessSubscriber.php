<?php

namespace Drupal\entity_module_test\EventSubscriber;

use Drupal\entity\QueryAccess\ConditionGroup;
use Drupal\entity\QueryAccess\QueryAccessEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class QueryAccessSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      'entity.query_access.entity_test_enhanced' => 'onQueryAccess',
    ];
  }

  /**
   * Modifies the access conditions based on the current user.
   *
   * This is just a convenient example for testing. A real subscriber would
   * ignore the account and extend the conditions to cover additional factors,
   * such as a custom entity field.
   *
   * @param \Drupal\entity\QueryAccess\QueryAccessEvent $event
   *   The event.
   */
  public function onQueryAccess(QueryAccessEvent $event) {
    $conditions = $event->getConditions();
    $email = $event->getAccount()->getEmail();

    if ($email == 'user1@example.com') {
      // This user should not have access to any entities.
      $conditions->alwaysFalse();
    }
    elseif ($email == 'user2@example.com') {
      // This user should have access to entities with the IDs 1, 2, and 3.
      // The query access handler might have already set ->alwaysFalse()
      // due to the user not having any other access, so we make sure
      // to undo it with $conditions->alwaysFalse(TRUE).
      $conditions->alwaysFalse(FALSE);
      $conditions->addCondition('id', ['1', '2', '3']);
    }
    elseif ($email == 'user3@example.com') {
      // This user should only have access to entities assigned to "marketing",
      // or unassigned entities.
      $conditions->alwaysFalse(FALSE);
      $conditions->addCondition((new ConditionGroup('OR'))
        ->addCondition('assigned', NULL, 'IS NULL')
        // Confirm that explicitly specifying the property name works.
        ->addCondition('assigned.value', 'marketing')
      );
    }
  }

}
