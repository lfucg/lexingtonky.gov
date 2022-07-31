<?php

namespace Drupal\Tests\honeypot\Unit\Integration\Event;

use Drupal\rules\Core\RulesEventManager;
use Drupal\Tests\rules\Unit\Integration\Event\EventTestBase as RulesEventTestBase;

/**
 * Base class containing common code for Honeypot event tests.
 *
 * @group honeypot
 *
 * @requires module rules
 */
abstract class EventTestBase extends RulesEventTestBase {

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    // Must enable our module to make our plugins discoverable.
    $this->enableModule('honeypot');

    // Tell the plugin manager where to look for plugins.
    $this->moduleHandler->getModuleDirectories()
      ->willReturn(['honeypot' => __DIR__ . '/../../../../../']);

    // Create a real plugin manager with a mock moduleHandler.
    $this->eventManager = new RulesEventManager($this->moduleHandler->reveal(), $this->entityTypeBundleInfo->reveal());
  }

}
