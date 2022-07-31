<?php

namespace Drupal\search_api_solr\EventSubscriber;

use Drupal\Core\Config\ConfigCrudEvent;
use Drupal\Core\Config\ConfigEvents;
use Drupal\Core\Config\ConfigInstallerInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Provides a ConfigSubscriber that adds language-specific Solr Field Types.
 *
 * Whenever a new language is enabled this EventSubscriber installs all
 * available Solr Field Types for that language.
 */
class ConfigSubscriber implements EventSubscriberInterface {

  /**
   * The Config Installer.
   *
   * @var \Drupal\Core\Config\ConfigInstallerInterface
   */
  protected $configInstaller;

  /**
   * Constructs a ConfigSubscriber object.
   *
   * @param \Drupal\Core\Config\ConfigInstallerInterface $configInstaller
   *   The Config Installer.
   */
  public function __construct(ConfigInstallerInterface $configInstaller) {
    $this->configInstaller = $configInstaller;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[ConfigEvents::SAVE][] = ['onConfigSave'];
    return $events;
  }

  /**
   * Installs all available Solr Field Types for a new language.
   *
   * @param \Drupal\Core\Config\ConfigCrudEvent $event
   *   The configuration event.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\search_api\SearchApiException
   */
  public function onConfigSave(ConfigCrudEvent $event) {
    // To prevent config to be installed during a site install with existing config
    // we need to check that situation through the install state.
    global $install_state;
    if (isset($install_state['parameters']['existing_config'])) {
      return;
    }

    $saved_config = $event->getConfig();

    if (preg_match('@^language\.entity\.(.+)@', $saved_config->getName(), $matches) &&
        $matches[1] != LanguageInterface::LANGCODE_NOT_SPECIFIED && $matches[1] != LanguageInterface::LANGCODE_NOT_APPLICABLE) {
      $restrict_by_dependency = [
        'module' => 'search_api_solr',
      ];
      // installOptionalConfig will not replace existing configs and it contains
      // a dependency check so we need not perform any checks ourselves.
      $this->configInstaller->installOptionalConfig(NULL, $restrict_by_dependency);

      // If a new language is added, the existing indexes must be re-indexed to
      // fill the language-specific sort fields for the new language.
      foreach (search_api_solr_get_servers() as $server) {
        foreach ($server->getIndexes() as $index) {
          if ($index->status() && !$index->isReadOnly() && !$index->isReindexing()) {
            $index->reindex();
          }
        }
      }
    }
    elseif (preg_match('@^search_api_solr\.solr_field_type\..+@', $saved_config->getName(), $matches)) {
      \Drupal::messenger()
        ->addMessage(t('A new Solr field type has been installed due to configuration changes. It is advisable to download and deploy an updated config.zip to your Solr server.'), MessengerInterface::TYPE_WARNING);
    }
    elseif (preg_match('@^search_api_solr\.solr_cache\..+@', $saved_config->getName(), $matches) || preg_match('@^search_api_solr\.solr_request\..+@', $saved_config->getName(), $matches)) {
      \Drupal::messenger()
        ->addMessage(t('There have been some configuration changes. It is advisable to download and deploy an updated config.zip to your Solr server.'), MessengerInterface::TYPE_WARNING);
    }
  }

}
