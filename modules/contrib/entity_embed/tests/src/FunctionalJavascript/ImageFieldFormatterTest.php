<?php

namespace Drupal\Tests\entity_embed\FunctionalJavascript;

use Drupal\FunctionalJavascriptTests\WebDriverTestBase;
use Drupal\editor\Entity\Editor;
use Drupal\filter\Entity\FilterFormat;

/**
 * Tests ckeditor integration.
 *
 * @group entity_embed
 */
class ImageFieldFormatterTest extends WebDriverTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'field_ui',
    'node',
    'file',
    'image',
    'ckeditor',
    'entity_embed',
  ];

  /**
   * The test button.
   *
   * @var Drupal\embed\Entity\EmbedButton
   */
  protected $button;

  /**
   * The test administrative user.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $adminUser;

  /**
   * Created file entity.
   *
   * @var \Drupal\file\FileInterface
   */
  protected $image;

  /**
   * File created with invalid image.
   *
   * @var \Drupal\file\FileInterface
   */
  protected $invalidImage;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->button = $this->container->get('entity_type.manager')
      ->getStorage('embed_button')
      ->create([
        'label' => 'Image Embed',
        'id' => 'image_embed',
        'type_id' => 'entity',
        'type_settings' => [
          'entity_type' => 'file',
          'display_plugins' => [
            'image:image',
          ],
          'entity_browser' => '',
          'entity_browser_settings' => [
            'display_review' => FALSE,
          ],
        ],
        'icon_uuid' => NULL,
      ]);

    $this->button->save();

    $format = FilterFormat::create([
      'format' => 'embed_test',
      'name' => 'Embed format',
      'filters' => [],
    ]);
    $format->save();
    $editor = Editor::create([
      'format' => 'embed_test',
      'editor' => 'ckeditor',
      'settings' => [
        'toolbar' => [
          'rows' => [],
        ],
      ],
    ]);
    $editor->save();

    $this->drupalCreateContentType(['type' => 'page', 'name' => 'Basic page']);

    $this->adminUser = $this->drupalCreateUser([
      'access administration pages',
      'administer filters',
      'administer display modes',
      'administer embed buttons',
      'administer site configuration',
      'administer display modes',
      'administer content types',
      'administer node display',
      'access content',
      'create page content',
      $format->getPermissionName(),
    ]);

    $this->drupalLogin($this->adminUser);

    // Create a sample image to embed.
    $entity_embed_path = $this->container->get('module_handler')
      ->getModule('entity_embed')
      ->getPath();
    file_unmanaged_copy($entity_embed_path . '/js/plugins/drupalentity/entity.png', 'public://example1.png');

    // Resize the test image so that it will be scaled down during token
    // replacement.
    $image1 = $this->container->get('image.factory')->get('public://example1.png');
    $image1->resize(500, 500);
    $image1->save();

    $this->image = $this->container->get('entity_type.manager')
      ->getStorage('file')
      ->create([
        'uri' => 'public://example1.png',
        'status' => 1,
      ]);
    $this->image->save();

    $this->invalidImage = $this->container->get('entity_type.manager')
      ->getStorage('file')
      ->create([
        'uri' => 'public://nonexistentimage.jpg',
        'filename' => 'nonexistentimage.jpg',
        'status' => 1,
      ]);
    $this->invalidImage->save();
  }

  /**
   * Test invalid image error.
   */
  public function testInvalidImageError() {
    $this->drupalGet('admin/config/content/formats/manage/embed_test');
    $this->assertSession()->buttonExists('Show group names')->press();
    $this->assertSession()->waitForElementVisible('css', '.ckeditor-add-new-group');
    $this->assertSession()->buttonExists('Add group')->press();
    $this->assertSession()->waitForElementVisible('css', '[name="group-name"]')->setValue('Embeds');
    $this->assertSession()->buttonExists('Apply')->press();
    $target = $this->assertSession()->waitForElementVisible('css', 'ul.ckeditor-toolbar-group-buttons');
    $imageButton = $this->assertSession()->elementExists('xpath', '//li[@data-drupal-ckeditor-button-name="' . $this->button->id() . '"]');
    $imageButton->dragTo($target);
    $page = $this->getSession()->getPage();

    $page->checkField('filters[entity_embed][status]');
    $page->checkField('filters[filter_html][status]');
    $this->assertSession()->buttonExists('Save configuration')->press();
    $this->assertSession()->responseContains('The text format <em class="placeholder">Embed format</em> has been updated.');

    $filterFormat = $this->container->get('entity_type.manager')
      ->getStorage('filter_format')
      ->load('embed_test');

    $settings = $filterFormat->filters('filter_html')->settings;
    $allowed_html = $settings['allowed_html'];

    $this->assertContains('drupal-entity data-entity-type data-entity-uuid data-entity-embed-display data-entity-embed-display-settings data-align data-caption data-embed-button', $allowed_html);

    $this->drupalGet('/node/add/page');
    $this->assertSession()->elementExists('css', 'a.cke_button__' . $this->button->id())->click();
    $this->assertSession()->waitForId('drupal-modal');
    $title = $this->invalidImage->label() . ' (' . $this->invalidImage->id() . ')';
    $this->assertSession()->fieldExists('entity_id')->setValue($title);
    $this->assertSession()->elementExists('css', 'button.js-button-next')->click();
    $this->assertSession()->assertWaitOnAjaxRequest();
    $this->assertSession()->responseContains('The selected image "' . $this->invalidImage->label() . '" is invalid.');
    $title = $this->image->label() . ' (' . $this->image->id() . ')';
    $this->assertSession()->fieldExists('entity_id')->setValue($title);
    $this->assertSession()->elementExists('css', 'button.js-button-next')->click();
    $this->assertSession()->assertWaitOnAjaxRequest();
    $this->createScreenshot(\Drupal::root() . '/sites/default/files/simpletest/screen.png');
    $this->assertSession()->responseNotContains('The selected image "' . $this->image->label() . '" is invalid.');
    $this->assertSession()->responseContains('Selected entity');
    $this->assertSession()->responseContains($this->image->label());
  }

}
