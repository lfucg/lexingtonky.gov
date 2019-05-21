<?php

namespace Drupal\Tests\entity_embed\Functional;

use Drupal\language\Entity\ConfigurableLanguage;
use Drupal\node\Entity\Node;

/**
 * Tests the entity_embed filter.
 *
 * @group entity_embed
 */
class EntityEmbedFilterTest extends EntityEmbedTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = [
    'content_translation',
    'file',
    'image',
    'entity_embed',
    'entity_embed_test',
    'node',
    'ckeditor',
  ];

  /**
   * Tests the entity_embed filter.
   *
   * Ensures that entities are getting rendered when correct data attributes
   * are passed. Also tests situations when embed fails.
   */
  public function testFilter() {
    $assert_session = $this->assertSession();

    // Tests entity embed using entity ID and view mode.
    $content = '<drupal-entity data-entity-type="node" data-entity-id="' . $this->node->id() . '" data-view-mode="teaser">This placeholder should not be rendered.</drupal-entity>';
    $settings = [];
    $settings['type'] = 'page';
    $settings['title'] = 'Test entity embed with entity-id and view-mode';
    $settings['body'] = [['value' => $content, 'format' => 'custom_format']];
    $node = $this->drupalCreateNode($settings);
    $this->drupalGet('node/' . $node->id());
    $this->assertNoRaw('<drupal-entity data-entity-type="node" data-entity');
    $this->assertText($this->node->body->value, 'Embedded node exists in page');
    $this->assertNoText(strip_tags($content), 'Placeholder does not appear in the output when embed is successful.');
    $this->assertRaw('<div data-entity-type="node" data-entity-id="1" data-view-mode="teaser" data-entity-uuid="' . $this->node->uuid() . '" data-langcode="en" data-entity-embed-display="entity_reference:entity_reference_entity_view" data-entity-embed-display-settings="teaser" class="embedded-entity">');

    // Tests that embedded entity is not rendered if not accessible.
    $this->node->setPublished(FALSE)->save();
    $settings = [];
    $settings['type'] = 'page';
    $settings['title'] = 'Test un-accessible entity embed with entity-id and view-mode';
    $settings['body'] = [['value' => $content, 'format' => 'custom_format']];
    $node = $this->drupalCreateNode($settings);
    $this->drupalGet('node/' . $node->id());
    $this->assertNoRaw('<drupal-entity data-entity-type="node" data-entity');
    $this->assertNoText($this->node->body->value, 'Embedded node does not exist in the page.');
    $this->assertNoText(strip_tags($content), 'Placeholder does not appear in the output when embed is successful.');
    // Tests that embedded entity is displayed to the user who has the view
    // unpublished content permission.
    $this->createRole(['view own unpublished content'], 'access_unpublished');
    $this->webUser->addRole('access_unpublished');
    $this->webUser->save();
    $this->drupalGet('node/' . $node->id());
    $this->assertNoRaw('<drupal-entity data-entity-type="node" data-entity');
    $this->assertText($this->node->body->value, 'Embedded node exists in the page.');
    $this->assertNoText(strip_tags($content), 'Placeholder does not appear in the output when embed is successful.');
    $this->assertRaw('<div data-entity-type="node" data-entity-id="1" data-view-mode="teaser" data-entity-uuid="' . $this->node->uuid() . '" data-langcode="en" data-entity-embed-display="entity_reference:entity_reference_entity_view" data-entity-embed-display-settings="teaser" class="embedded-entity">');
    $this->webUser->removeRole('access_unpublished');
    $this->webUser->save();
    $this->node->setPublished(TRUE)->save();

    // Tests entity embed using entity UUID and view mode.
    $content = '<drupal-entity data-entity-type="node" data-entity-uuid="' . $this->node->uuid() . '" data-view-mode="teaser">This placeholder should not be rendered.</drupal-entity>';
    $settings = [];
    $settings['type'] = 'page';
    $settings['title'] = 'Test entity embed with entity-uuid and view-mode';
    $settings['body'] = [['value' => $content, 'format' => 'custom_format']];
    $node = $this->drupalCreateNode($settings);
    $this->drupalGet('node/' . $node->id());
    $this->assertNoRaw('<drupal-entity data-entity-type="node" data-entity');
    $this->assertText($this->node->body->value, 'Embedded node exists in page.');
    $this->assertNoText(strip_tags($content), 'Placeholder does not appear in the output when embed is successful.');
    $this->assertRaw('<div data-entity-type="node" data-entity-uuid="' . $this->node->uuid() . '" data-view-mode="teaser" data-langcode="en" data-entity-embed-display="entity_reference:entity_reference_entity_view" data-entity-embed-display-settings="teaser" class="embedded-entity">');
    $this->assertCacheTag('foo:' . $this->node->id());

    // Ensure that placeholder is not replaced when embed is unsuccessful.
    $content = '<drupal-entity data-entity-type="node" data-entity-id="InvalidID" data-view-mode="teaser">This placeholder should be rendered since specified entity does not exists.</drupal-entity>';
    $settings = [];
    $settings['type'] = 'page';
    $settings['title'] = 'Test that placeholder is retained when specified entity does not exists';
    $settings['body'] = [['value' => $content, 'format' => 'custom_format']];
    $node = $this->drupalCreateNode($settings);
    $this->drupalGet('node/' . $node->id());
    $this->assertNoRaw('<drupal-entity data-entity-type="node" data-entity');
    $this->assertNoText(strip_tags($content), 'Placeholder does not appear in the output when embed is unsuccessful.');

    // Ensure that UUID is preferred over ID when both attributes are present.
    $sample_node = $this->drupalCreateNode();
    $content = '<drupal-entity data-entity-type="node" data-entity-id="' . $sample_node->id() . '" data-entity-uuid="' . $this->node->uuid() . '" data-view-mode="teaser">This placeholder should not be rendered.</drupal-entity>';
    $settings = [];
    $settings['type'] = 'page';
    $settings['title'] = 'Test that entity-uuid is preferred over entity-id when both attributes are present';
    $settings['body'] = [['value' => $content, 'format' => 'custom_format']];
    $node = $this->drupalCreateNode($settings);
    $this->drupalGet('node/' . $node->id());
    $this->assertNoRaw('<drupal-entity data-entity-type="node" data-entity');
    $this->assertText($this->node->body->value, 'Entity specifed with UUID exists in the page.');
    $this->assertNoText($sample_node->body->value, 'Entity specifed with ID does not exists in the page.');
    $this->assertNoText(strip_tags($content), 'Placeholder does not appear in the output when embed is successful.');
    $this->assertRaw('<div data-entity-type="node" data-entity-id="' . $sample_node->id() . '" data-entity-uuid="' . $this->node->uuid() . '" data-view-mode="teaser" data-langcode="en" data-entity-embed-display="entity_reference:entity_reference_entity_view" data-entity-embed-display-settings="teaser" class="embedded-entity">');

    // Test deprecated 'default' Entity Embed Display plugin.
    $content = '<drupal-entity data-entity-type="node" data-entity-uuid="' . $this->node->uuid() . '" data-entity-embed-display="default" data-entity-embed-display-settings=\'{"view_mode":"teaser"}\'>This placeholder should not be rendered.</drupal-entity>';
    $settings = [];
    $settings['type'] = 'page';
    $settings['title'] = 'Test entity embed with entity-embed-display and data-entity-embed-display-settings';
    $settings['body'] = [['value' => $content, 'format' => 'custom_format']];
    $node = $this->drupalCreateNode($settings);
    $this->drupalGet('node/' . $node->id());
    $this->assertText($this->node->body->value, 'Embedded node exists in page.');
    $this->assertNoText(strip_tags($content), 'Placeholder does not appear in the output when embed is successful.');
    $this->assertRaw('<div data-entity-type="node" data-entity-uuid="' . $this->node->uuid() . '" data-entity-embed-display="entity_reference:entity_reference_entity_view" data-entity-embed-display-settings="teaser" data-langcode="en" class="embedded-entity">');

    // Ensure that Entity Embed Display plugin is preferred over view mode when
    // both attributes are present.
    $content = '<drupal-entity data-entity-type="node" data-entity-uuid="' . $this->node->uuid() . '" data-entity-embed-display="default" data-entity-embed-display-settings=\'{"view_mode":"full"}\' data-view-mode="some-invalid-view-mode" data-align="left" data-caption="test caption">This placeholder should not be rendered.</drupal-entity>';
    $settings = [];
    $settings['type'] = 'page';
    $settings['title'] = 'Test entity embed with entity-embed-display and data-entity-embed-display-settings';
    $settings['body'] = [['value' => $content, 'format' => 'custom_format']];
    $node = $this->drupalCreateNode($settings);
    $this->drupalGet('node/' . $node->id());
    $this->assertText($this->node->body->value, 'Embedded node exists in page with the view mode specified by entity-embed-settings.');
    $this->assertNoText(strip_tags($content), 'Placeholder does not appear in the output when embed is successful.');
    $this->assertSession()->elementExists('css', 'figure.caption-drupal-entity.align-left div.embedded-entity[data-entity-embed-display="entity_reference:entity_reference_entity_view"][data-entity-embed-display-settings="full"][data-entity-type="node"][data-entity-uuid="' . $this->node->uuid() . '"][data-view-mode="some-invalid-view-mode"][data-langcode="en"]');
    $this->assertSession()->elementTextContains('css', 'figure.caption-drupal-entity.align-left figcaption', 'test caption');

    // Ensure the embedded node doesn't contain data tags on the full page.
    $this->drupalGet('node/' . $this->node->id());
    $this->assertNoRaw('data-align="left"', 'Align data attribute not found.');
    $this->assertNoRaw('data-caption="test caption"', 'Caption data attribute not found.');

    // Test that tag of container element is not replaced when it's not
    // <drupal-entity>.
    $content = '<not-drupal-entity data-entity-type="node" data-entity-id="' . $this->node->id() . '" data-view-mode="teaser">this placeholder should not be rendered.</not-drupal-entity>';
    $settings = [];
    $settings['type'] = 'page';
    $settings['title'] = 'test entity embed with entity-id and view-mode';
    $settings['body'] = [['value' => $content, 'format' => 'custom_format']];
    $node = $this->drupalCreateNode($settings);
    $this->drupalget('node/' . $node->id());
    $this->assertNoText($this->node->body->value, 'embedded node exists in page');
    $this->assertRaw('</not-drupal-entity>');
    $content = '<div data-entity-type="node" data-entity-id="' . $this->node->id() . '" data-view-mode="teaser">this placeholder should not be rendered.</div>';
    $settings = [];
    $settings['type'] = 'page';
    $settings['title'] = 'test entity embed with entity-id and view-mode';
    $settings['body'] = [['value' => $content, 'format' => 'custom_format']];
    $node = $this->drupalCreateNode($settings);
    $this->drupalget('node/' . $node->id());
    $this->assertNoText($this->node->body->value, 'embedded node exists in page');
    $this->assertRaw('<div data-entity-type="node" data-entity-id');

    // Test that attributes are correctly added when image formatter is used.
    /** @var \Drupal\file\FileInterface $image */
    $image = $this->getTestFile('image');
    $image->setPermanent();
    $image->save();
    $content = '<drupal-entity data-entity-type="file" data-entity-uuid="' . $image->uuid() . '" data-entity-embed-display="image:image" data-entity-embed-display-settings=\'{"image_style":"","image_link":""}\' data-align="left" data-caption="test caption" alt="This is alt text" title="This is title text">This placeholder should not be rendered.</drupal-entity>';
    $settings = [];
    $settings['type'] = 'page';
    $settings['title'] = 'test entity image formatter';
    $settings['body'] = [['value' => $content, 'format' => 'custom_format']];
    $node = $this->drupalCreateNode($settings);
    $this->drupalget('node/' . $node->id());
    $this->assertSession()->elementExists('css', 'figure.caption-drupal-entity.align-left div.embedded-entity[alt="This is alt text"][data-entity-embed-display="image:image"][data-entity-type="file"][data-entity-uuid="' . $image->uuid() . '"][title="This is title text"][data-langcode="en"] img[src][alt="This is alt text"][title="This is title text"]');
    $this->assertSession()->elementTextContains('css', 'figure.caption-drupal-entity.align-left figcaption', 'test caption');

    // data-entity-embed-settings is replaced with
    // data-entity-embed-display-settings. Check to see if
    // data-entity-embed-settings is still working.
    $content = '<drupal-entity data-entity-type="node" data-entity-uuid="' . $this->node->uuid() . '" data-entity-embed-display="entity_reference:entity_reference_label" data-entity-embed-settings=\'{"link":"0"}\' data-align="left" data-caption="test caption">This placeholder should not be rendered.</drupal-entity>';
    $settings = [];
    $settings['type'] = 'page';
    $settings['title'] = 'Test entity embed with data-entity-embed-settings';
    $settings['body'] = [['value' => $content, 'format' => 'custom_format']];
    $node = $this->drupalCreateNode($settings);
    $this->drupalGet('node/' . $node->id());
    $this->assertSession()->elementExists('css', 'figure.caption-drupal-entity.align-left div.embedded-entity[data-entity-embed-display="entity_reference:entity_reference_label"][data-entity-type="node"][data-entity-uuid="' . $this->node->uuid() . '"][data-langcode="en"]');
    $this->assertSession()->elementTextContains('css', 'figure.caption-drupal-entity.align-left div.embedded-entity', 'Embed Test Node');
    $this->assertSession()->elementTextContains('css', 'figure.caption-drupal-entity.align-left figcaption', 'test caption');

    // Tests entity embed using custom attribute and custom data- attribute.
    $content = '<drupal-entity data-foo="bar" foo="bar" data-entity-type="node" data-entity-uuid="' . $this->node->uuid() . '" data-view-mode="teaser">This placeholder should not be rendered.</drupal-entity>';
    $settings = [];
    $settings['type'] = 'page';
    $settings['title'] = 'Test entity embed with custom attributes';
    $settings['body'] = [['value' => $content, 'format' => 'custom_format']];
    $node = $this->drupalCreateNode($settings);
    $this->drupalGet('node/' . $node->id());
    $this->assertRaw('<div data-foo="bar" foo="bar" data-entity-type="node" data-entity-uuid="' . $this->node->uuid() . '" data-view-mode="teaser" data-langcode="en" data-entity-embed-display="entity_reference:entity_reference_entity_view" data-entity-embed-display-settings="teaser" class="embedded-entity">');

    // Tests the placeholder for missing entities.
    $embedded_node = $this->drupalCreateNode([
      'type' => 'page',
      'title' => 'Embedded node',
      'body' => [['value' => 'Embedded text content', 'format' => 'custom_format']],
    ]);
    $content = '<drupal-entity data-entity-type="node" data-entity-uuid="' . $embedded_node->uuid() . '" data-view-mode="default"></drupal-entity>';
    $settings = [];
    $settings['type'] = 'page';
    $settings['title'] = 'Host node';
    $settings['body'] = [['value' => $content, 'format' => 'custom_format']];
    $node = $this->drupalCreateNode($settings);
    $this->drupalGet('node/' . $node->id());
    $assert_session->pageTextContains('Embedded text content');
    $assert_session->elementNotExists('css', 'img[alt^="Deleted content encountered, site owner alerted"]');
    $embedded_node->delete();
    $this->drupalGet('node/' . $node->id());
    $assert_session->pageTextNotContains('Embedded text content');
    $placeholder = $assert_session->elementExists('css', 'img[alt^="Deleted content encountered, site owner alerted"]');
    $this->assertTrue(strpos($placeholder->getAttribute('src'), 'core/modules/media/images/icons/no-thumbnail.png') > 0);
  }

  /**
   * Tests the filter in different translation contexts.
   */
  public function testTranslation() {
    $content = '<drupal-entity data-entity-type="node" data-entity-uuid="' . $this->node->uuid() . '" data-entity-embed-display="entity_reference:entity_reference_label" data-entity-embed-settings=\'{"link":"0"}\' data-align="left" data-caption="test caption">This placeholder should not be rendered.</drupal-entity>';

    ConfigurableLanguage::createFromLangcode('pt-br')->save();
    $host_entity = $this->drupalCreateNode([
      'type' => 'page',
      'body' => [
        'value' => $content,
        'format' => 'custom_format',
      ],
    ]);
    $this->drupalGet($host_entity->toUrl());
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains($host_entity->getTitle());
    $this->assertSession()->pageTextContains($this->node->getTitle());

    // Translate the host entity, but keep the same body; only change the title.
    $translated_host_entity = $host_entity->addTranslation('pt-br')
      ->getTranslation('pt-br')
      ->setTitle('Em portugues')
      ->set('body', $host_entity->get('body')->getValue());
    $translated_host_entity->save();
    $this->drupalGet('/pt-br/node/' . $host_entity->id());
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains($translated_host_entity->getTitle());
    // The embedded node does not have a Portuguese translation, so it should
    // display in English.
    $this->assertSession()->pageTextContains($this->node->getTitle());

    // Translate the embedded entity to the same language as the host entity.
    $this->node = Node::load($this->node->id());
    $this->node->addTranslation('pt-br')
      ->getTranslation('pt-br')
      ->setTitle('Embed em portugues')
      ->save();
    $this->getSession()->reload();
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains($translated_host_entity->getTitle());
    // The translated host entity now should show the matching translation of
    // the embedded entity.
    $this->assertSession()->pageTextContains($this->node->getTranslation('pt-br')->getTitle());

    // Change the translated host entity to explicitly embed the untranslated
    // entity.
    $translated_host_entity->body->value = '<drupal-entity data-entity-type="node" data-entity-uuid="' . $this->node->uuid() . '" data-entity-embed-display="entity_reference:entity_reference_label" data-entity-embed-settings=\'{"link":"0"}\' data-align="left" data-caption="test caption" data-langcode="' . $host_entity->language()->getId() . '">This placeholder should not be rendered.</drupal-entity>';
    $translated_host_entity->save();
    $this->getSession()->reload();
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains($translated_host_entity->getTitle());
    $this->assertSession()->pageTextContains($this->node->getTitle());

    // Change the untranslated host entity to explicitly embed the Portuguese
    // translation of the embedded entity.
    $host_entity->body->value = '<drupal-entity data-entity-type="node" data-entity-uuid="' . $this->node->uuid() . '" data-entity-embed-display="entity_reference:entity_reference_label" data-entity-embed-settings=\'{"link":"0"}\' data-align="left" data-caption="test caption" data-langcode="pt-br">This placeholder should not be rendered.</drupal-entity>';
    $host_entity->save();
    $this->drupalGet('/node/' . $host_entity->id());
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains($host_entity->getTitle());
    $this->assertSession()->pageTextContains($this->node->getTranslation('pt-br')->getTitle());

    // Change the untranslated host entity to explicitly embed a non-existing
    // translation of the embedded entity; this should fall back to the default
    // translation.
    $host_entity->body->value = '<drupal-entity data-entity-type="node" data-entity-uuid="' . $this->node->uuid() . '" data-entity-embed-display="entity_reference:entity_reference_label" data-entity-embed-settings=\'{"link":"0"}\' data-align="left" data-caption="test caption" data-langcode="nl">This placeholder should not be rendered.</drupal-entity>';
    $host_entity->save();
    $this->getSession()->reload();
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains($host_entity->getTitle());
    $this->assertSession()->pageTextContains($this->node->getTitle());

    // Change the translated host entity to explicitly embed a non-existing
    // translation of the embedded entity; this should fall back to the default
    // translation.
    $translated_host_entity->body->value = '<drupal-entity data-entity-type="node" data-entity-uuid="' . $this->node->uuid() . '" data-entity-embed-display="entity_reference:entity_reference_label" data-entity-embed-settings=\'{"link":"0"}\' data-align="left" data-caption="test caption" data-langcode="nl">This placeholder should not be rendered.</drupal-entity>';
    $translated_host_entity->save();
    $this->drupalGet('/pt-br/node/' . $host_entity->id());
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains($translated_host_entity->getTitle());
    $this->assertSession()->pageTextContains($this->node->getTitle());
  }

}
