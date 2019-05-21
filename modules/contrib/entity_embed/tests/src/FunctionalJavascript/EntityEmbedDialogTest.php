<?php

namespace Drupal\Tests\entity_embed\FunctionalJavascript;

/**
 * Tests the entity_embed dialog controller and route.
 *
 * @group entity_embed
 * @requires function Drupal\FunctionalJavascriptTests\WebDriverTestBase::assertSession
 */
class EntityEmbedDialogTest extends EntityEmbedTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['image'];

  /**
   * Tests the entity embed button markup.
   */
  public function testEntityEmbedButtonMarkup() {
    // Ensure that the route is accessible with a valid embed button.
    // 'Node' embed button is provided by default by the module and hence the
    // request must be successful.
    $this->getEmbedDialog('custom_format', 'node');

    // Ensure form structure of the 'select' step and submit form.
    $this->assertFieldByName('entity_id', '', 'Entity ID/UUID field is present.');

    // Check that 'Next' is a primary button.
    $this->assertFieldByXPath('//input[contains(@class, "button--primary")]', 'Next', 'Next is a primary button');

    $title = $this->node->getTitle() . ' (' . $this->node->id() . ')';
    $this->assertSession()->fieldExists('entity_id')->setValue($title);
    $this->assertSession()->buttonExists('Next')->press();
    $this->assertSession()->assertWaitOnAjaxRequest();
    $plugins = [
      'entity_reference:entity_reference_label',
      'entity_reference:entity_reference_entity_id',
      'view_mode:node.full',
      'view_mode:node.rss',
      'view_mode:node.search_index',
      'view_mode:node.search_result',
      'view_mode:node.teaser',
    ];
    foreach ($plugins as $plugin) {
      $this->assertSession()->optionExists('Display as', $plugin);
    }

    $this->container->get('config.factory')->getEditable('entity_embed.settings')
      ->set('rendered_entity_mode', TRUE)->save();
    $this->container->get('plugin.manager.entity_embed.display')->clearCachedDefinitions();

    $this->getEmbedDialog('custom_format', 'node');
    $title = $this->node->getTitle() . ' (' . $this->node->id() . ')';
    $this->assertSession()->fieldExists('entity_id')->setValue($title);
    $this->assertSession()->buttonExists('Next')->press();
    $this->assertSession()->assertWaitOnAjaxRequest();

    $plugins = [
      'entity_reference:entity_reference_label',
      'entity_reference:entity_reference_entity_id',
      'entity_reference:entity_reference_entity_view',
    ];
    foreach ($plugins as $plugin) {
      $this->assertSession()->optionExists('Display as', $plugin);
    }
  }

  /**
   * Retrieves an embed dialog based on given parameters.
   *
   * @param string $filter_format_id
   *   ID of the filter format.
   * @param string $embed_button_id
   *   ID of the embed button.
   *
   * @return string
   *   The retrieved HTML string.
   */
  public function getEmbedDialog($filter_format_id = NULL, $embed_button_id = NULL) {
    $url = 'entity-embed/dialog';
    if (!empty($filter_format_id)) {
      $url .= '/' . $filter_format_id;
      if (!empty($embed_button_id)) {
        $url .= '/' . $embed_button_id;
      }
    }
    return $this->drupalGet($url);
  }

}
