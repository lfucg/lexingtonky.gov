<?php

namespace Drupal\search_api_pantheon\Plugin\SolrConnector;

use Drupal\Component\EventDispatcher\ContainerAwareEventDispatcher;
use Drupal\search_api_pantheon\Solarium\PantheonCurl;
use Drupal\Core\Form\FormStateInterface;
use Drupal\search_api_solr\Solarium\EventDispatcher\Psr14Bridge;
use Drupal\search_api_solr_legacy\Plugin\SolrConnector\Solr36Connector;
use Solarium\Client;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\search_api_pantheon\SchemaPoster;

/**
 * Pantheon Solr connector.
 *
 * @SolrConnector(
 *   id = "pantheon",
 *   label = @Translation("Pantheon"),
 *   description = @Translation("A connector for Pantheon's Solr 3.6 server")
 * )
 */
class PantheonSolrConnector extends Solr36Connector {

  /**
   * The event dispatcher.
   *
   * @var \Drupal\Component\EventDispatcher\ContainerAwareEventDispatcher
   */
  protected $eventDispatcher;

  /**
   * Variable SchemaPoster.
   *
   * @var \Drupal\search_api_pantheon\SchemaPoster
   */
  protected $schemaPoster;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, array $plugin_definition, SchemaPoster $schema_poster, ContainerAwareEventDispatcher $eventDispatcher) {
    $configuration += $this->internalConfiguration();
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->schemaPoster = $schema_poster;
    if (class_exists('\Drupal\Component\EventDispatcher\Event')) {
      // Drupal >= 9.1.
      $this->eventDispatcher = $eventDispatcher;
    }
    else {
      // Drupal <= 9.0.
      $this->eventDispatcher = new Psr14Bridge($eventDispatcher);
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('search_api_pantheon.schema_poster'),
      $container->get('event_dispatcher')
    );
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

    return $pantheon_specific_configuration + $this->defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'schema' => '',
      'solr_version' => '3.6.2',
    ] + parent::defaultConfiguration();
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
    $directory = new \RecursiveDirectoryIterator('modules');
    $flattened = new \RecursiveIteratorIterator($directory);
    $files = new \RegexIterator($flattened, '/schema.xml$/');

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

    $form['schema'] = [
      '#type' => 'radios',
      '#title' => $this->t('Schema file'),
      '#options' => $this->findSchemaFiles(),
      '#description' => $this->t("Select a Solr schema file to be POSTed to Pantheon's Solr server"),
      '#default_value' => $this->configuration['schema'],
    ];

    return $form;
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
   * Create a Client.
   */
  protected function createClient(array &$configuration) {
    $configuration[self::QUERY_TIMEOUT] = 5;
    $adapter = new PantheonCurl($configuration);
    return new Client($adapter, $this->eventDispatcher);
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
    $ping = parent::pingServer();
    // If the ping fails, there is a good chance it is because the code
    // is being run on a new multidev environment in which the schema has not
    // yet been posted.
    if ($ping === FALSE) {
      $this->postSchema();
      // Try again after posting the schema.
      return parent::pingServer();
    }
    else {
      return $ping;
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function getDataFromHandler($handler, $reset = FALSE) {
    // Ensure server is up and post schema if necessary, ex new Multi-dev.
    $this->pingServer();
    return parent::getDataFromHandler($handler, $reset);
  }

}
