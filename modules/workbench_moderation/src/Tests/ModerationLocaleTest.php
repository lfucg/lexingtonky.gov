<?php

/**
 * @file
 * Contains \Drupal\workbench_moderation\Tests\ModerationLocaleTest.
 */

namespace Drupal\workbench_moderation\Tests;

use Drupal\Core\Language\Language;
use Drupal\Core\Language\LanguageInterface;
use Drupal\language\Entity\ConfigurableLanguage;

/**
 * Test workbench_moderation functionality with localization and translation.
 *
 * @group workbench_moderation
 */
class ModerationLocaleTest extends ModerationStateTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['node', 'workbench_moderation', 'locale', 'content_translation'];

  /**
   * Test that an article can be translated and its translations can be
   * moderated separately as core does.
   */
  function testTranslateModeratedContent() {
    $this->drupalLogin($this->rootUser);

    // Enable moderation on Article node type.
    $this->createContentTypeFromUI('Article', 'article', TRUE, ['draft', 'published', 'archived'], 'draft');

    // Add French language.
    $edit = [
      'predefined_langcode' => 'fr',
    ];
    $this->drupalPostForm('admin/config/regional/language/add', $edit, t('Add language'));

    // Enable content translation on articles.
    $this->drupalGet('admin/config/regional/content-language');
    $edit = [
      'entity_types[node]' => TRUE,
      'settings[node][article][translatable]' => TRUE,
      'settings[node][article][settings][language][language_alterable]' => TRUE,
    ];
    $this->drupalPostForm(NULL, $edit, t('Save configuration'));

    // Create an article in English.
    $edit = [
      'title[0][value]' => 'English node',
      'langcode[0][value]' => 'en',
    ];
    $this->drupalPostForm('node/add/article', $edit, t('Save and Create New Draft'));
    $this->assertText(t('Article English node has been created.'));
    $english_node = $this->drupalGetNodeByTitle('English node');

    // Add a French translation.
    $this->drupalGet('node/' . $english_node->id() . '/translations');
    $this->clickLink(t('Add'));
    $edit = [
      'title[0][value]' => 'French node',
    ];
    $this->drupalPostForm(NULL, $edit, t('Save and Create New Draft (this translation)'));
    $this->assertText(t('Article French node has been updated.'));
    $english_node = $this->drupalGetNodeByTitle('English node', TRUE);
    $french_node = $english_node->getTranslation('fr');

    // Publish the English article and check that the translation stays
    // unpublished.
    $this->drupalPostForm('node/' . $english_node->id() . '/edit', [], t('Save and Publish (this translation)'));
    $this->assertText(t('Article English node has been updated.'));
    $english_node = $this->drupalGetNodeByTitle('English node', TRUE);
    $french_node = $english_node->getTranslation('fr');
    $this->assertEqual($english_node->moderation_state->target_id, 'published');
    $this->assertTrue($english_node->isPublished());
    $this->assertEqual($french_node->moderation_state->target_id, 'draft');
    $this->assertFalse($french_node->isPublished());

    // Create another article with its translation. This time we will publish
    // the translation first.
    $edit = [
      'title[0][value]' => 'Another node',
    ];
    $this->drupalPostForm('node/add/article', $edit, t('Save and Create New Draft'));
    $this->assertText(t('Article Another node has been created.'));
    $english_node = $this->drupalGetNodeByTitle('Another node');

    // Add a French translation.
    $this->drupalGet('node/' . $english_node->id() . '/translations');
    $this->clickLink(t('Add'));
    $edit = [
      'title[0][value]' => 'Translated node',
    ];
    $this->drupalPostForm(NULL, $edit, t('Save and Create New Draft (this translation)'));
    $this->assertText(t('Article Translated node has been updated.'));
    $english_node = $this->drupalGetNodeByTitle('Another node', TRUE);
    $french_node = $english_node->getTranslation('fr');

    // Publish the translation and check that the source language version stays
    // unpublished
    $this->drupalPostForm('fr/node/' . $english_node->id() . '/edit', [], t('Save and Publish (this translation)'));
    $this->assertText(t('Article Translated node has been updated.'));
    $english_node = $this->drupalGetNodeByTitle('Another node', TRUE);
    $french_node = $english_node->getTranslation('fr');
    $this->assertEqual($french_node->moderation_state->target_id, 'published');
    $this->assertTrue($french_node->isPublished());
    $this->assertEqual($english_node->moderation_state->target_id, 'draft');
    $this->assertFalse($english_node->isPublished());

    // Now check that we can create a new draft of the translation and then
    // publish it.
    $edit = [
      'title[0][value]' => 'New draft of translated node',
    ];
    $this->drupalPostForm('fr/node/' . $english_node->id() . '/edit', $edit, t('Save and Create New Draft (this translation)'));
    $this->assertText(t('Article New draft of translated node has been updated.'));
    $english_node = $this->drupalGetNodeByTitle('Another node', TRUE);
    $french_node = $english_node->getTranslation('fr');
    $this->assertEqual($french_node->moderation_state->target_id, 'published');
    $this->assertTrue($french_node->isPublished());
    $this->assertEqual($french_node->getTitle(), 'Translated node', 'The default revision of the published translation remains the same.');

    // Publish the draft.
    $edit = [
      'new_state' => 'published',
    ];
    $this->drupalPostForm('fr/node/' . $english_node->id() . '/latest', [], t('Apply'));
    $this->assertText(t('The moderation state has been updated.'));
    $english_node = $this->drupalGetNodeByTitle('Another node', TRUE);
    $french_node = $english_node->getTranslation('fr');
    $this->assertEqual($french_node->moderation_state->target_id, 'published');
    $this->assertTrue($french_node->isPublished());
    $this->assertEqual($french_node->getTitle(), 'New draft of translated node', 'The draft has replaced the published revision.');

    // Publish the English article before testing the archive transition.
    $this->drupalPostForm('node/' . $english_node->id() . '/edit', [], t('Save and Publish (this translation)'));
    $this->assertText(t('Article Another node has been updated.'));

    // Archive the node and its translation.
    $this->drupalPostForm('node/' . $english_node->id() . '/edit', [], t('Save and Archive (this translation)'));
    $this->assertText(t('Article Another node has been updated.'));
    $this->drupalPostForm('fr/node/' . $english_node->id() . '/edit', [], t('Save and Archive (this translation)'));
    $this->assertText(t('Article New draft of translated node has been updated.'));
    $english_node = $this->drupalGetNodeByTitle('Another node', TRUE);
    $french_node = $english_node->getTranslation('fr');
    $this->assertEqual($english_node->moderation_state->target_id, 'archived');
    $this->assertFalse($english_node->isPublished());
    $this->assertEqual($french_node->moderation_state->target_id, 'archived');
    $this->assertFalse($french_node->isPublished());
  }

}
