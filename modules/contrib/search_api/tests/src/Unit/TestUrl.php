<?php

namespace Drupal\Tests\search_api\Unit;

use Drupal\Core\Url;
use Drupal\Tests\UnitTestCase;

/**
 * Provides a mock URL object.
 */
class TestUrl extends Url {

  /**
   * Constructs a new class instance.
   *
   * @param string $path
   *   The internal path for this URL.
   */
  public function __construct(string $path) {
    $this->internalPath = $path;
  }

  /**
   * {@inheritdoc}
   */
  public function toString($collect_bubbleable_metadata = FALSE) {
    UnitTestCase::assertFalse($collect_bubbleable_metadata);
    if (!empty($this->options['absolute'])) {
      return 'http://www.example.com' . $this->internalPath;
    }
    return $this->internalPath;
  }

}
