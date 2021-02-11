<?php

namespace Drupal\Tests\metatag\Functional;

use Drupal\language\Entity\ConfigurableLanguage;
use Drupal\metatag\Entity\MetatagDefaults;
use Drupal\Tests\BrowserTestBase;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Ensures that the Metatag config translations work correctly.
 *
 * @group metatag
 */
class MetatagConfigTranslationTest extends BrowserTestBase {

  use StringTranslationTrait;

  /**
   * Profile to use.
   *
   * @var string
   */
  protected $profile = 'testing';

  /**
   * Admin user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $adminUser;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'metatag',
    'language',
    'config_translation',
    'node',
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * Permissions to grant admin user.
   *
   * @var array
   */
  protected $permissions = [
    // From Metatag.
    'administer meta tags',

    // From system module, in order to access the /admin pages.
    'access administration pages',

    // From language module.
    'administer languages',

    // From config_translations module.
    'translate configuration',
  ];

  /**
   * Sets the test up.
   */
  protected function setUp() {
    parent::setUp();
    $this->adminUser = $this->drupalCreateUser($this->permissions);
    $this->drupalLogin($this->adminUser);

    // Enable the French language.
    ConfigurableLanguage::createFromLangcode('fr')->save();
  }

  /**
   * Confirm the config defaults show on the translations page.
   */
  public function testConfigTranslationsExist() {
    // Ensure the config shows on the admin form.
    $this->drupalGet('admin/config/regional/config-translation');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertText($this->t('Metatag defaults'));

    // Load the main metatag_defaults config translation page.
    $this->drupalGet('admin/config/regional/config-translation/metatag_defaults');
    $this->assertSession()->statusCodeEquals(200);
    // @todo Update this to confirm the H1 is loaded.
    $this->assertRaw($this->t('Metatag defaults'));

    // Load all of the Metatag defaults.
    $defaults = \Drupal::configFactory()->listAll('metatag.metatag_defaults');

    /** @var \Drupal\Core\Config\ConfigManagerInterface $config_manager */
    $config_manager = \Drupal::service('config.manager');

    // Confirm each of the configs is available on the translation form.
    foreach ($defaults as $config_name) {
      if ($config_entity = $config_manager->loadConfigEntityByName($config_name)) {
        $this->assertText($config_entity->label());
      }
    }

    // Confirm that each config translation page can be loaded.
    foreach ($defaults as $config_name) {
      if ($config_entity = $config_manager->loadConfigEntityByName($config_name)) {
        $this->drupalGet('admin/config/search/metatag/' . $config_entity->id() . '/translate');
        $this->assertSession()->statusCodeEquals(200);
      }
      else {
        $this->error('Unable to load a Metatag default config: ' . $config_name);
      }
    }
  }

  /**
   * Confirm the global configs are translatable page.
   */
  public function testConfigTranslations() {
    // Add something to the Global config.
    $this->drupalGet('admin/config/search/metatag/global');
    $this->assertSession()->statusCodeEquals(200);
    $edit = [
      'title' => 'Test title',
      'description' => 'Test description',
    ];
    $this->drupalPostForm(NULL, $edit, $this->t('Save'));
    $this->assertSession()->statusCodeEquals(200);
    $this->assertText($this->t('Saved the Global Metatag defaults.'));

    // Confirm the config has languages available to translate into.
    $this->drupalGet('admin/config/search/metatag/global/translate');
    $this->assertSession()->statusCodeEquals(200);

    // Load the translation form.
    $this->drupalGet('admin/config/search/metatag/global/translate/fr/add');
    $this->assertSession()->statusCodeEquals(200);

    // Confirm the meta tag fields are shown on the form. Confirm the fields and
    // values separately to make it easier to pinpoint where the problem is if
    // one should fail.
    $this->assertFieldByName('translation[config_names][metatag.metatag_defaults.global][tags][title]');
    $this->assertFieldByName('translation[config_names][metatag.metatag_defaults.global][tags][title]', $edit['title']);
    $this->assertFieldByName('translation[config_names][metatag.metatag_defaults.global][tags][description]');
    $this->assertFieldByName('translation[config_names][metatag.metatag_defaults.global][tags][description]', $edit['description']);

    // Confirm the form can be saved correctly.
    $edit = [
      'translation[config_names][metatag.metatag_defaults.global][tags][title]' => 'Le title',
      'translation[config_names][metatag.metatag_defaults.global][tags][description]' => 'Le description',
    ];
    $this->drupalPostForm(NULL, $edit, $this->t('Save translation'));
    $this->assertSession()->statusCodeEquals(200);
    $this->assertText($this->t('Successfully saved French translation'));

    // Delete the node metatag defaults to simplify the test.
    MetatagDefaults::load('node')->delete();

    // Create a node in french, request default tags for it. Ensure that the
    // config translation language is afterwards still/again set to EN and
    // tags are returned in FR.
    $this->drupalCreateContentType(['type' => 'page']);
    $node = $this->drupalCreateNode([
      'title' => 'Metatag Test FR',
      'langcode' => 'fr',
    ]);

    $language_manager = \Drupal::languageManager();
    $this->assertEquals('en', $language_manager->getConfigOverrideLanguage()->getId());
    $fr_default_tags = metatag_get_default_tags($node);
    $this->assertEquals('Le title', $fr_default_tags['title']);
    $this->assertEquals('Le description', $fr_default_tags['description']);
    $this->assertEquals('en', $language_manager->getConfigOverrideLanguage()->getId());

    // Delete the default tags as well to test the early return.
    MetatagDefaults::load('global')->delete();
    $fr_default_tags = metatag_get_default_tags($node);
    $this->assertNull($fr_default_tags);
    $this->assertEquals('en', $language_manager->getConfigOverrideLanguage()->getId());
  }

}
