<?php

namespace Drupal\Tests\imce\Kernel\Plugin\CKEditorPlugin;

use Drupal\KernelTests\KernelTestBase;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\editor\Entity\Editor;
use Drupal\imce\Plugin\CKEditorPlugin\Imce;
use Drupal\Tests\user\Traits\UserCreationTrait;
use Drupal\text\Plugin\Field\FieldWidget\TextareaWithSummaryWidget;

/**
 * Kernel tests for Imce plugins for CKEditor.
 *
 * @group imce
 */
class ImceTest extends KernelTestBase {

  use StringTranslationTrait;
  use UserCreationTrait;

  /**
   * The Imce ckeditor plugin.
   *
   * @var \Drupal\imce\Plugin\CKEditorPlugin\Imce
   */
  public $imce;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'user',
    'system',
    'imce',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->imce = new Imce([], "text_textarea_with_summary", $this->getPluginDefinations());
  }

  /**
   * Test getDependencies().
   */
  public function testGetDependencies() {
    $dependencies = $this->imce->getDependencies($this->createMock(Editor::class));
    $this->assertTrue(is_array($dependencies));
    $this->assertArraySubset(['drupalimage', 'drupalimagecaption'], $dependencies);;
  }

  /**
   * Get plugins definations.
   *
   * @return array
   *   Return plugins definations.
   */
  public function getPluginDefinations() {
    return [
      "field_types" => [
        0 => "text_with_summary",
      ],
      "multiple_values" => FALSE,
      "id" => "text_textarea_with_summary",
      "label" => $this->t("Text area with a summary"),
      "class" => TextareaWithSummaryWidget::class,
      "provider" => "text",
    ];
  }

  /**
   * Test getButtons().
   */
  public function testGetButtons() {
    $buttons = $this->imce->getButtons();
    $this->assertTrue(is_array($buttons));
  }

  /**
   * Test getFile().
   */
  public function testGetFile() {
    $pathFile = $this->imce->getFile();
    $this->assertTrue(is_string($pathFile));
  }

  /**
   * Test getConfig().
   */
  public function testGetConfig() {
    $config = $this->imce->getConfig($this->createMock(Editor::class));
    $this->assertTrue(is_array($config));
    $this->assertCount(2, $config);
  }

  /**
   * Test imageIcon().
   */
  public function testImageIcon() {
    $imageIcon = $this->imce->imageIcon();
    $this->assertTrue(is_string($imageIcon));
    $this->assertTrue(file_exists($imageIcon));
  }

  /**
   * Test linkIcon().
   */
  public function testLinkIcon() {
    $linkIcon = $this->imce->linkIcon();
    $this->assertTrue(is_string($linkIcon));
    $this->assertTrue(file_exists($linkIcon));
  }

}
