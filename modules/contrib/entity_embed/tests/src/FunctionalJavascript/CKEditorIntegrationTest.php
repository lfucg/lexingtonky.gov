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
class CKEditorIntegrationTest extends WebDriverTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'field_ui',
    'node',
    'ckeditor',
    'views',
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
   * A test node to be used for embedding.
   *
   * @var \Drupal\node\NodeInterface
   */
  protected $node;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->button = $this->container->get('entity_type.manager')
      ->getStorage('embed_button')
      ->load('node');
    $settings = $this->button->getTypeSettings();
    $settings['display_plugins'] = [
      'entity_reference:entity_reference_label',
    ];
    $this->button->set('type_settings', $settings);
    $this->button->save();

    $format = FilterFormat::create([
      'format' => 'embed_test',
      'name' => 'Embed format',
      'filters' => [],
    ]);
    $format->save();

    Editor::create([
      'format' => 'embed_test',
      'editor' => 'ckeditor',
      'settings' => [
        'toolbar' => [
          'rows' => [],
        ],
      ],
    ])->save();

    // Create a page content type.
    $this->drupalCreateContentType([
      'type' => 'page',
      'name' => 'Basic page',
    ]);

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
      'edit own page content',
      $format->getPermissionName(),
    ]);

    $this->drupalLogin($this->adminUser);

    // Create a sample node.
    $this->drupalCreateNode([
      'type' => 'page',
      'title' => 'Billy Bones',
      'body' => [
        'value' => 'He lacks two fingers.',
      ],
    ]);

    $this->drupalCreateNode([
      'type' => 'page',
      'title' => 'Long John Silver',
      'body' => [
        'value' => 'A one-legged seafaring man.',
      ],
    ]);
  }

  /**
   * Test integration with Filter, Editor and Ckeditor.
   */
  public function testIntegration() {
    $this->drupalGet('admin/config/content/formats/manage/embed_test');

    $page = $this->getSession()->getPage();

    $page->checkField('filters[entity_embed][status]');
    $page->checkField('filters[filter_html][status]');

    // Add "Embeds" toolbar button group to the active toolbar.
    $this->assertSession()->buttonExists('Show group names')->press();
    $this->assertSession()->waitForElementVisible('css', '.ckeditor-add-new-group');
    $this->assertSession()->buttonExists('Add group')->press();
    $this->assertSession()->waitForElementVisible('css', '[name="group-name"]')->setValue('Embeds');
    $this->assertSession()->buttonExists('Apply')->press();

    // Verify the <drupal-entity> tag is not yet allowed.
    $allowed_html = $this->assertSession()->fieldExists('filters[filter_html][settings][allowed_html]')->getValue();
    $this->assertNotContains('drupal-entity', $allowed_html);

    // Verify that after dragging the Entity Embed CKEditor plugin button into
    // the active toolbar, the <drupal-entity> tag is allowed, as well as some
    // attributes.
    $target = $this->assertSession()->waitForElementVisible('css', 'ul.ckeditor-toolbar-group-buttons');
    $buttonElement = $this->assertSession()->elementExists('xpath', '//li[@data-drupal-ckeditor-button-name="' . $this->button->id() . '"]');
    $buttonElement->dragTo($target);
    $allowed_html_updated = $this->assertSession()
      ->fieldExists('filters[filter_html][settings][allowed_html]')
      ->getValue();
    $this->assertContains('drupal-entity data-entity-type data-entity-uuid data-entity-embed-display data-entity-embed-display-settings data-align data-caption data-embed-button', $allowed_html_updated);

    $this->assertSession()->buttonExists('Save configuration')->press();
    $this->assertSession()->responseContains('The text format <em class="placeholder">Embed format</em> has been updated.');
    $filterFormat = $this->container->get('entity_type.manager')
      ->getStorage('filter_format')
      ->load('embed_test');

    $settings = $filterFormat->filters('filter_html')->settings;
    $allowed_html = $settings['allowed_html'];

    $this->assertContains('drupal-entity data-entity-type data-entity-uuid data-entity-embed-display data-entity-embed-display-settings data-align data-caption data-embed-button', $allowed_html);

    // Verify that the Entity Embed button shows up and results in an
    // operational entity embedding experience in the text editor.
    $this->drupalGet('/node/add/page');
    $this->assignNameToCkeditorIframe();
    $this->getSession()->switchToIFrame('ckeditor');
    $this->assertSession()->pageTextNotContains('Billy Bones');
    $this->getSession()->switchToIFrame();
    $this->assertSession()->elementExists('css', 'a.cke_button__' . $this->button->id())->click();
    $this->assertSession()->waitForId('drupal-modal');
    $this->assertSession()
      ->fieldExists('entity_id')
      ->setValue('Billy Bones (1)');
    $this->assertSession()->elementExists('css', 'button.js-button-next')->click();
    $this->assertSession()->assertWaitOnAjaxRequest();
    $this->assertSession()->responseContains('Selected entity');
    $this->assertSession()->responseContains('Billy Bones');
    $this->assertSession()->elementExists('css', 'button.button--primary')->press();
    $this->assertSession()->assertWaitOnAjaxRequest();
    // Verify that the embedded entity gets a preview inside the text editor.
    $this->getSession()->switchToIFrame('ckeditor');
    $this->assertSession()->pageTextContains('Billy Bones');
    $this->getSession()->switchToIFrame();
    $this->getSession()
      ->getPage()
      ->find('css', 'input[name="title[0][value]"]')
      ->setValue('Pirates');
    $this->assertSession()->buttonExists('Save')->press();
    // Verify that the embedded entity is rendered by the filter for end users.
    $this->assertSession()->responseContains('Billy Bones');

    $this->drupalGet('/node/3/edit');
    $this->assignNameToCkeditorIframe();

    // Verify that the text editor previews the current embedded entity.
    $this->getSession()->switchToIFrame('ckeditor');
    $this->assertSession()->waitForText('Billy Bones');
    $this->getSession()->switchToIFrame();

    // Test opening the dialog and switching embedded nodes.
    $select_and_edit_embed = "var editor = CKEDITOR.instances['edit-body-0-value'];
      var entityEmbed = editor.widgets.getByElement(editor.editable().findOne('div'));
      entityEmbed.focus();
      editor.execCommand('editdrupalentity');";
    $this->getSession()->executeScript($select_and_edit_embed);

    $this->assertSession()
      ->waitForElementVisible('css', 'div.ui-dialog-buttonset')
      ->findButton('Back')
      ->click();

    $this->assertSession()->assertWaitOnAjaxRequest();

    $this->assertSession()
      ->fieldExists('entity_id')
      ->setValue('Long John Silver (2)');

    $this->assertSession()->elementExists('css', 'button.js-button-next')->click();
    $this->assertSession()->assertWaitOnAjaxRequest();
    $this->assertSession()->responseContains('Selected entity');
    $this->assertSession()->responseContains('Long John Silver');
    $this->assertSession()->elementExists('css', 'button.button--primary')->press();
    $this->assertSession()->assertWaitOnAjaxRequest();

    // Verify that the text editor previews the updated embedded entity.
    $this->getSession()->switchToIFrame('ckeditor');
    $this->assertSession()->waitForText('Long John Silver');
    $this->getSession()->switchToIFrame();
    $this->assertSession()->buttonExists('Save')->press();
    // Verify that the embedded entity is rendered by the filter for end users.
    $this->assertSession()->responseContains('Long John Silver');
  }

  /**
   * Assigns a name to the CKEditor iframe, to allow use of ::switchToIFrame().
   *
   * @see \Behat\Mink\Session::switchToIFrame()
   */
  protected function assignNameToCkeditorIframe() {
    $javascript = <<<JS
(function(){
  document.getElementsByClassName('cke_wysiwyg_frame')[0].id = 'ckeditor';
})()
JS;
    $this->getSession()->evaluateScript($javascript);
  }

}
