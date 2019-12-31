<?php

/**
 * @file
 * Provide a connection to Pantheon's Solr instance.
 */

namespace Drupal\search_api_pantheon\Plugin\SolrConnector;

use Drupal\search_api_solr\Plugin\SolrConnector\StandardSolrConnector;
use Drupal\Core\Annotation\Translation;
use Drupal\Core\Form\FormStateInterface;
use Drupal\search_api_solr\Annotation\SolrConnector;
use Solarium\Core\Client\Endpoint;
use Solarium\Core\Client\Request;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\search_api_pantheon\SchemaPoster;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\search_api_solr\SolrBackendInterface;
use Solarium\Client;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;

/**
 * Standard Solr connector.
 *
 * @SolrConnector(
 *   id = "pantheon",
 *   label = @Translation("Pantheon"),
 *   description = @Translation("A connector for Pantheon's Solr server")
 * )
 */
class PantheonSolrConnector extends StandardSolrConnector {

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, array $plugin_definition, SchemaPoster $schema_poster) {
    $configuration += $this->internalConfiguration();
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->schemaPoster = $schema_poster;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $plugin = new static($configuration, $plugin_id, $plugin_definition, $container->get('search_api_pantheon.schema_poster'));

    /** @var \Drupal\Core\StringTranslation\TranslationInterface $translation */
    $translation = $container->get('string_translation');
    $plugin->setStringTranslation($translation);

    return $plugin;
  }

  /**
   * This configuration is needed by the parent class.
   *
   * However, as far as the Drupal Config Management sysytem is concerned
   * the only exportable, user-changable configuration is the schema file.
   */
  protected function internalConfiguration() {
    $pantheon_specific_configuration = [];
    if (!empty($_ENV['PANTHEON_ENVIRONMENT'])) {
      $pantheon_specific_configuration = [
        'scheme' => 'https',
        'host' => $_ENV['PANTHEON_INDEX_HOST'],
        'port' => $_ENV['PANTHEON_INDEX_PORT'],
        'path' => '/sites/self/environments/' . $_ENV['PANTHEON_ENVIRONMENT'] . '/index',
      ];
    }

    return $pantheon_specific_configuration + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return array(
      'schema' => '',
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getConfiguration() {
    return $this->configuration;
  }

  /**
   * Find schema files that can be posted to the Solr server.
   *
   * @return array
   *   The returned array will be used by Form API.
   */
  public function findSchemaFiles() {
    $return = [];
    $directory = new RecursiveDirectoryIterator('modules');
    $flattened = new RecursiveIteratorIterator($directory);
    $files = new RegexIterator($flattened, '/schema.xml$/');

    foreach ($files as $file) {
      $relative_path = str_replace(DRUPAL_ROOT . '/', '', $file->getRealPath());
      $return[$relative_path] = $relative_path;
    }
    return $return;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {

    $form['schema'] = array(
      '#type' => 'radios',
      '#title' => $this->t('Schema file'),
      '#options' => $this->findSchemaFiles(),
      '#description' => $this->t('Select a Solr schema file to be POSTed to Pantheon\'s Solr server'),
      '#default_value' => $this->configuration['schema'],
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    // Setting the configuration here will allow the simple configuration,
    // just the schema file, to be saved to the Search API server config entity.
    // When this plugin is reloaded, $this->configuration, will be repopulated
    // with $this->internalConfiguration().
    $this->configuration = $form_state->getValues();
    $this->postSchema();
  }

  /**
   * Prepares the connection to the Solr server.
   */
  protected function connect() {
    if (!$this->solr) {
      $this->solr = new Client();

      // The parent method is overridden so that this alternate adapter class
      // can be set. This line is the only difference from the parent method.
      $this->solr->setAdapter('Drupal\search_api_pantheon\Solarium\PantheonCurl');

      $this->solr->createEndpoint($this->configuration + ['key' => 'core'], TRUE);
      $this->attachServerEndpoint();
    }
  }

  /**
   * Post the configured schema file to the Solr Service.
   */
  protected function postSchema() {
    return $this->schemaPoster->postSchema($this->configuration['schema']);
  }

  /**
   * {@inheritdoc}
   */
  public function pingServer() {
    // The path used in the parent class, admin/info/system, fails.
    // I don't know why.
    $ping = $this->doPing(['handler' => 'admin/system'], 'server');
    // If the ping fails, there is a good chance it is because the code
    // is being run on a new multidev environment in which the schema has not
    // yet been posted.
    if ($ping === FALSE) {
      $this->postSchema();
      // Try again after posting the schema.
      return $this->doPing(['handler' => 'admin/system'], 'server');
    }
    else {
      return $ping;
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function getDataFromHandler($endpoint, $handler, $reset = FALSE) {
    // First make sure the server is up.
    // If a multidev environment has just been made,
    // it may be necessary to post the schema.
    $this->pingServer();
    return parent::getDataFromHandler($endpoint, $handler, $reset = FALSE);
  }
}
