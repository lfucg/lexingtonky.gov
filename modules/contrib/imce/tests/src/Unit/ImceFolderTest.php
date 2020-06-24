<?php

namespace Drupal\Tests\imce\Unit;

use Drupal\imce\ImceFolder;
use Drupal\Tests\UnitTestCase;

/**
 * Test ImceFile.
 *
 * @group imce
 */
class ImceFolderTest extends UnitTestCase {

  /**
   * The Imce Folder.
   *
   * @var \Drupal\imce\ImceFolder
   */
  protected $imceFolder;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->imceFolder = $this->createMock(ImceFolder::class);
  }

  /**
   * Test type.
   */
  public function testType() {
    $this->assertNotEmpty($this->imceFolder->type);
    $this->assertTrue(is_string($this->imceFolder->type));
    $this->assertEquals('folder', $this->imceFolder->type);
  }

}
