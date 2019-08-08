<?php

namespace Drupal\Tests\entity_embed\FunctionalJavascript;

use Drupal\editor\Entity\Editor;
use Drupal\filter\Entity\FilterFormat;

/**
 * Tests text format & text editor configuration UI validation.
 *
 * @group entity_embed
 */
class ConfigurationUiTest extends EntityEmbedTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'ckeditor',
    'entity_embed',
  ];

  /**
   * The test administrative user.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $adminUser;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $format = FilterFormat::create([
      'format' => 'embed_test',
      'name' => 'Embed format',
      'filters' => [],
    ]);
    $format->save();

    Editor::create([
      'format' => $format->id(),
      'editor' => 'ckeditor',
      'settings' => [
        'toolbar' => [
          'rows' => [
            [
              [
                'name' => 'Tools',
                'items' => [
                  'Source',
                  'Undo',
                  'Redo',
                ],
              ],
            ],
          ],
        ],
      ],
    ])->save();

    $this->adminUser = $this->drupalCreateUser([
      'administer filters',
      $format->getPermissionName(),
    ]);

    $this->drupalLogin($this->adminUser);
  }

  /**
   * Test integration with Filter and Text Editor form validation.
   *
   * @param bool $filter_html_status
   *   Whether to enable filter_html.
   * @param bool $entity_embed_status
   *   Whether to enabled entity_embed.
   * @param string|false $allowed_html
   *   The allowed HTML to set.  Set to 'default' to test 'drupal-entity' is
   *   missing or FALSE to leave the properly alone.
   * @param string $expected_error_message
   *   The error message that should display.
   *
   * @dataProvider providerTestValidations
   * @dataProvider providerTestValidationWhenAdding
   */
  public function testValidationWhenAdding($filter_html_status, $entity_embed_status, $allowed_html, $expected_error_message) {
    $this->drupalGet('admin/config/content/formats/add');

    // Enable the `filter_html` and `entity_embed` filters, and select CKEditor
    // as the text editor.
    $page = $this->getSession()->getPage();
    $page->fillField('name', 'Test Format');
    $this->showHiddenFields();
    $page->findField('format')->setValue('test_format');

    if ($filter_html_status) {
      $page->checkField('filters[filter_html][status]');
    }
    if ($entity_embed_status) {
      $page->checkField('filters[entity_embed][status]');
    }
    $page->selectFieldOption('editor[editor]', 'ckeditor');

    // Verify that after dragging the Entity Embed CKEditor plugin button into
    // the active toolbar, the <drupal-entity> tag is allowed, as well as some
    // attributes.
    $target = $this->assertSession()->waitForElementVisible('css', 'ul.ckeditor-toolbar-group-buttons');
    $button_element = $this->assertSession()->elementExists('xpath', '//li[@data-drupal-ckeditor-button-name="test_media_entity_embed"]');
    $button_element->dragTo($target);

    if ($allowed_html == 'default' && $entity_embed_status) {
      // Unfortunately the <drupal-entity> tag is not yet allowed due to
      // https://www.drupal.org/project/drupal/issues/2763075.
      $allowed_html = $this->assertSession()->fieldExists('filters[filter_html][settings][allowed_html]')->getValue();
      $this->assertNotContains('drupal-entity', $allowed_html);
    }
    elseif (!empty($allowed_html)) {
      $page->fillField('filters[filter_html][settings][allowed_html]', $allowed_html);
    }

    $this->assertSession()->buttonExists('Save configuration')->press();

    if ($expected_error_message) {
      $this->assertSession()->pageTextNotContains('Added text format Test Format.');
      $this->assertSession()->pageTextContains($expected_error_message);
    }
    else {
      $this->assertSession()->pageTextContains('Added text format Test Format.');
    }
  }

  /**
   * Data provider for testValidationWhenAdding().
   */
  public function providerTestValidationWhenAdding() {
    return [
      'Tests validation when drupal-entity not added.' => [
        'filters[filter_html][status]' => TRUE,
        'filters[entity_embed][status]' => TRUE,
        'allowed_html' => 'default',
        'expected_error_message' => 'The Media Entity Embed button requires <drupal-entity> among the allowed HTML tags.',
      ],
    ];
  }

  /**
   * Test integration with Filter and Text Editor form validation.
   *
   * @param bool $filter_html_status
   *   Whether to enable filter_html.
   * @param bool $entity_embed_status
   *   Whether to enabled entity_embed.
   * @param string $allowed_html
   *   The allowed HTML to set.  Set to 'default' to test 'drupal-entity' is
   *   present or FALSE to leave the properly alone.
   * @param string $expected_error_message
   *   The error message that should display.
   *
   * @dataProvider providerTestValidations
   * @dataProvider providerTestValidationWhenEditing
   */
  public function testValidationWhenEditing($filter_html_status, $entity_embed_status, $allowed_html, $expected_error_message) {
    $this->drupalGet('admin/config/content/formats/manage/embed_test');

    // Enable the `filter_html` and `entity_embed` filters, and select CKEditor
    // as the text editor.
    $page = $this->getSession()->getPage();

    if ($filter_html_status) {
      $page->checkField('filters[filter_html][status]');
    }
    if ($entity_embed_status) {
      $page->checkField('filters[entity_embed][status]');
    }
    $page->selectFieldOption('editor[editor]', 'ckeditor');

    // Verify that after dragging the Entity Embed CKEditor plugin button into
    // the active toolbar, the <drupal-entity> tag is allowed, as well as some
    // attributes.
    $target = $this->assertSession()->waitForElementVisible('css', 'ul.ckeditor-toolbar-group-buttons');
    $button_element = $this->assertSession()->elementExists('xpath', '//li[@data-drupal-ckeditor-button-name="test_media_entity_embed"]');
    $button_element->dragTo($target);

    if ($allowed_html == 'default' && $entity_embed_status) {
      $allowed_html = $this->assertSession()->fieldExists('filters[filter_html][settings][allowed_html]')->getValue();
      $this->assertContains('drupal-entity', $allowed_html);
    }
    elseif (!empty($allowed_html)) {
      $page->fillField('filters[filter_html][settings][allowed_html]', $allowed_html);
    }

    $this->assertSession()->buttonExists('Save configuration')->press();

    if ($expected_error_message) {
      $this->assertSession()->pageTextNotContains('The text format Embed format has been updated.');
      $this->assertSession()->pageTextContains($expected_error_message);
    }
    else {
      $this->assertSession()->pageTextContains('The text format Embed format has been updated.');
    }
  }

  /**
   * Data provider for testValidationWhenEditing().
   */
  public function providerTestValidationWhenEditing() {
    return [
      'Tests validation when drupal-entity not added.' => [
        'filters[filter_html][status]' => TRUE,
        'filters[entity_embed][status]' => TRUE,
        'allowed_html' => 'default',
        'expected_error_message' => FALSE,
      ],
    ];
  }

  /**
   * Data provider for testValidationWhenAdding() and
   * testValidationWhenEditing().
   */
  public function providerTestValidations() {
    return [
      'Tests that no filter_html occurs when filter_html not enabled.' => [
        'filters[filter_html][status]' => FALSE,
        'filters[entity_embed][status]' => TRUE,
        'allowed_html' => FALSE,
        'expected_error_message' => FALSE,
      ],
      'Tests validation when both filter_html and entity_embed are disabled.' => [
        'filters[filter_html][status]' => FALSE,
        'filters[entity_embed][status]' => FALSE,
        'allowed_html' => FALSE,
        'expected_error_message' => FALSE,
      ],
      'Tests validation when entity_embed filter not enabled and filter_html is enabled.' => [
        'filters[filter_html][status]' => TRUE,
        'filters[entity_embed][status]' => FALSE,
        'allowed_html' => 'default',
        'expected_error_message' => FALSE,
      ],
      'Tests validation when drupal-entity element has no attributes.' => [
        'filters[filter_html][status]' => TRUE,
        'filters[entity_embed][status]' => TRUE,
        'allowed_html' => "<a href hreflang> <em> <strong> <cite> <blockquote cite> <code> <ul type> <ol start type='1 A I'> <li> <dl> <dt> <dd> <h2 id='jump-*'> <h3 id> <h4 id> <h5 id> <h6 id> <drupal-entity>",
        'expected_error_message' => 'The <drupal-entity> tag in the allowed HTML tags is missing the following attributes: data-entity-type, data-entity-uuid, data-entity-embed-display, data-entity-embed-display-settings, data-align, data-caption, data-embed-button, alt, title.',
      ],
      'Tests validation when drupal-entity element lacks some required attributes.' => [
        'filters[filter_html][status]' => TRUE,
        'filters[entity_embed][status]' => TRUE,
        'allowed_html' => "<a href hreflang> <em> <strong> <cite> <blockquote cite> <code> <ul type> <ol start type='1 A I'> <li> <dl> <dt> <dd> <h2 id='jump-*'> <h3 id> <h4 id> <h5 id> <h6 id> <drupal-entity data-entity-type data-entity-uuid data-entity-embed-display data-entity-embed-display-settings data-align data-embed-button data-langcode>",
        'expected_error_message' => 'The <drupal-entity> tag in the allowed HTML tags is missing the following attributes: data-caption, alt, title.',
      ],
    ];
  }

}
