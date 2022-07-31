<?php

namespace Drupal\search_api_pantheon\Plugin\SolrConnector;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\PluginFormInterface;
use Drupal\search_api_solr\SolrConnector\SolrConnectorPluginBase;
use Drupal\search_api_solr\SolrConnectorInterface;
use Drupal\search_api_pantheon\Services\Endpoint as PantheonEndpoint;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Solarium\Client as SolariumClient;
use Solarium\Core\Client\Endpoint;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\search_api_pantheon\Services\PantheonGuzzle;
use Drupal\search_api_pantheon\Services\SolariumClient as PantheonSolariumClient;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Messenger\MessengerInterface;

/**
 * Pantheon Solr connector.
 *
 * @SolrConnector(
 *   id = "pantheon",
 *   label = @Translation("Pantheon Search Connector"),
 *   description = @Translation("Connection to Pantheon's Search server interface")
 * )
 */
class PantheonSolrConnector extends SolrConnectorPluginBase implements
    SolrConnectorInterface,
    PluginFormInterface,
    ContainerFactoryPluginInterface,
    LoggerAwareInterface {
  use LoggerAwareTrait;

  /**
   * @var object|null
   */
  protected $solr;

  /**
   * The PantheonGuzzle service.
   *
   * @var \Drupal\search_api_pantheon\Services\PantheonGuzzle
   */
  protected PantheonGuzzle $pantheonGuzzle;

  /**
   * The solarium client service.
   *
   * @var \Drupal\search_api_pantheon\Services\SolariumClient
   */
  protected PantheonSolariumClient $solariumClient;

  /**
   * The date formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected DateFormatterInterface $dateFormatter;

  /**
   * The messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * Class constructor.
   */
  public function __construct(
        array $configuration,
        $plugin_id,
        array $plugin_definition,
        LoggerChannelFactoryInterface $logger_factory,
        PantheonGuzzle $pantheon_guzzle,
        PantheonSolariumClient $solarium_client,
        DateFormatterInterface $date_formatter,
        MessengerInterface $messenger
    ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->pantheonGuzzle = $pantheon_guzzle;
    $this->solariumClient = $solarium_client;
    $this->dateFormatter = $date_formatter;
    $this->messenger = $messenger;
    $this->setLogger($logger_factory->get('PantheonSearch'));
    $this->configuration['core'] = self::getPlatformConfig()['core'];
    $this->configuration['schema'] = self::getPlatformConfig()['schema'];
    $this->connect();
  }

  /**
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   * @param array $configuration
   * @param string $plugin_id
   * @param mixed $plugin_definition
   *
   * @return \Drupal\search_api\Plugin\ConfigurablePluginBase|\Drupal\search_api_pantheon\Plugin\SolrConnector\PantheonSolrConnector|static
   * @throws \Exception
   */
  public static function create(
        ContainerInterface $container,
        array $configuration,
        $plugin_id,
        $plugin_definition
    ) {
    return new static(
          $configuration,
          $plugin_id,
          $plugin_definition,
          $container->get('logger.factory'),
          $container->get('search_api_pantheon.pantheon_guzzle'),
          $container->get('search_api_pantheon.solarium_client'),
          $container->get('date.formatter'),
          $container->get('messenger')
      );
  }

  /**
   * Returns platform-specific Solr configuration.
   *
   * @return array
   *   Pantheon platform Solr configuration.
   */
  public static function getPlatformConfig() {
    return [
      'scheme' => getenv('PANTHEON_INDEX_SCHEME'),
      'host' => getenv('PANTHEON_INDEX_HOST'),
      'port' => getenv('PANTHEON_INDEX_PORT'),
      'path' => getenv('PANTHEON_INDEX_PATH'),
      'core' => getenv('PANTHEON_INDEX_CORE'),
      'schema' => getenv('PANTHEON_INDEX_SCHEMA'),
    ];
  }

  /**
   * Returns TRUE if all platform-related configuration values are present.
   *
   * @return bool
   *   TRUE if all platform-related configuration values are present.
   */
  public static function isPlatformConfigPresent() {
    $config = self::getPlatformConfig();

    return count($config) === count(array_filter($config));
  }

  /**
   * @return array
   */
  public function defaultConfiguration() {
    return array_merge(
      parent::defaultConfiguration(),
      self::getPlatformConfig(),
      [
        'solr_version' => '8',
        'skip_schema_check' => TRUE,
      ]
    );
  }

  /**
   * Build form hook.
   *
   * @param array $form
   *   Form render array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state object.
   *
   * @return array
   *   Form render array.
   */
  public function buildConfigurationForm(
        array $form,
        FormStateInterface $form_state
    ) {
    $form = parent::buildConfigurationForm($form, $form_state);

    $fields = [
      'timeout',
      SolrConnectorInterface::INDEX_TIMEOUT,
      SolrConnectorInterface::OPTIMIZE_TIMEOUT,
      SolrConnectorInterface::FINALIZE_TIMEOUT,
      'commit_within',
    ];
    $form = array_filter(
      $form,
      function ($field_name) use ($fields) {
        return in_array($field_name, $fields, TRUE);
      },
      ARRAY_FILTER_USE_KEY
    );

    $form['notice'] = [
      '#type' => 'html_tag',
      '#tag' => 'h3',
      '#value' => $this->t("Other options are configured using environment variables on Pantheon.io's custom platform"),
    ];

    return $form;
  }

  /**
   * Form validate handler.
   *
   * @param array $form
   *   Form render array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state object.
   */
  public function validateConfigurationForm(
        array &$form,
        FormStateInterface $form_state
    ) {
  }

  /**
   * Form submit handler.
   *
   * @param array $form
   *   Form render array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state object.
   */
  public function submitConfigurationForm(
        array &$form,
        FormStateInterface $form_state
    ) {
    $configuration = array_merge($this->defaultConfiguration(), $form_state->getValues());

    $this->setConfiguration($configuration);

    // Exclude Platform configs.
    foreach (array_keys(self::getPlatformConfig()) as $key) {
      unset($this->configuration[$key]);
    }
  }

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function adjustTimeout(int $seconds, string $timeout = self::QUERY_TIMEOUT, ?Endpoint &$endpoint = NULL): int {
    $this->connect();

    if (!$endpoint) {
      $endpoint = $this->solr->getEndpoint();
    }

    $previous_timeout = $endpoint->getOption($timeout);
    $options = $endpoint->getOptions();
    $options[$timeout] = $seconds;
    $endpoint = new PantheonEndpoint($options, \Drupal::entityTypeManager());

    return $previous_timeout;
  }

  /**
   * Returns the default endpoint name.
   *
   * @return string
   *   The endpoint name.
   */
  public static function getDefaultEndpoint() {
    return PantheonEndpoint::DEFAULT_NAME;
  }

  /**
   * Stats Summary.
   *
   * @throws \JsonException
   */
  public function getStatsSummary() {
    $stats = [];
    try {
      $mbeansResponse = $this->getStatsQuery('admin/mbeans') ?? ['solr-mbeans' => []];
      $mbeans = new \ArrayIterator($mbeansResponse['solr-mbeans'] ?? []);
      for ($mbeans->rewind(); $mbeans->valid(); $mbeans->next()) {
        $current = $mbeans->current();
        $mbeans->next();
        if ($mbeans->valid() && is_string($current)) {
          $stats[$current] = $mbeans->current();
        }
      }
      $indexResponse = $this->getStatsQuery('admin/luke') ?? ['index' => []];
      $indexStats = $indexResponse['index'] ?? [];
    }
    catch (\Exception $e) {
      $this->messenger->addError(
        $this->t('Unable to get stats from server!')
      );
    }

    $summary = [
          '@pending_docs' => '',
          '@autocommit_time_seconds' => '',
          '@autocommit_time' => '',
          '@deletes_by_id' => '',
          '@deletes_by_query' => '',
          '@deletes_total' => '',
          '@schema_version' => '',
          '@core_name' => '',
          '@index_size' => '',
      ];

    if (empty($stats) || empty($indexStats)) {
      return $summary;
    }

    $max_time = -1;
    $update_handler_stats = $stats['UPDATE']['updateHandler']['stats'] ?? -1;
    $summary['@pending_docs'] =
            (int) $update_handler_stats['UPDATE.updateHandler.docsPending'] ?? -1;
    if (
          isset(
              $update_handler_stats['UPDATE.updateHandler.softAutoCommitMaxTime']
          )
      ) {
      $max_time =
                (int) $update_handler_stats['UPDATE.updateHandler.softAutoCommitMaxTime'];
    }
    $summary['@deletes_by_id'] =
            (int) $update_handler_stats['UPDATE.updateHandler.deletesById'] ?? -1;
    $summary['@deletes_by_query'] =
            (int) $update_handler_stats['UPDATE.updateHandler.deletesByQuery'] ?? -1;
    $summary['@core_name'] =
            $stats['CORE']['core']['class'] ??
            $this->t('No information available.');
    $summary['@index_size'] =
            $indexStats['numDocs'] ?? $this->t('No information available.');

    $summary['@autocommit_time_seconds'] = $max_time / 1000;
    $summary['@autocommit_time'] = $this->dateFormatter
      ->formatInterval($max_time / 1000);
    $summary['@deletes_total'] =
            (
              intval($summary['@deletes_by_id'] ?? 0)
              + intval($summary['@deletes_by_query'] ?? 0)
          ) ?? -1;
    $summary['@schema_version'] = $this->getSchemaVersionString(TRUE);
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function useTimeout(
        string $timeout = self::QUERY_TIMEOUT,
        ?Endpoint $endpoint = NULL
    ) {
  }

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\search_api_solr\SearchApiSolrException
   */
  public function viewSettings() {
    $view_settings = [];

    $view_settings[] = [
          'label' => $this->t('Pantheon Sitename'),
          'info' => $this->getEndpoint()->getCore(),
      ];
    $view_settings[] = [
          'label' => $this->t('Pantheon Environment'),
          'info' => getenv('PANTHEON_ENVIRONMENT'),
      ];
    $view_settings[] = [
          'label' => $this->t('Schema Version'),
          'info' => $this->getSchemaVersion(TRUE),
      ];

    $core_info = $this->getCoreInfo(TRUE);
    foreach ($core_info['core'] as $key => $value) {
      if (is_string($value)) {
        $view_settings[] = [
              'label' => ucwords($key),
              'info' => $value,
          ];
      }
    }

    return $view_settings;
  }

  /**
   * Override any other endpoints by getting the Pantheon Default endpoint.
   *
   * @param string $key
   *   The endpoint name (ignored).
   *
   * @return \Solarium\Core\Client\Endpoint
   *   The endpoint in question.
   */
  public function getEndpoint($key = 'search_api_solr') {
    return $this->solr->getEndpoint();
  }

  /**
   * Reaload the Solr Core.
   *
   * @return bool
   *   Success or Failure.
   */
  public function reloadCore() {
    $this->logger->notice(
          $this->t('Reload Core action for Pantheon Solr is automatic when Schema is updated.')
      );
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function getFile($file = NULL) {
    $query = [
      'action' => 'VIEW',
    ];
    if ($file) {
      $query['file'] = $file;
    }
    return $this->pantheonGuzzle->get('admin/file', [
      'query' => $query,
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function getServerInfo($reset = FALSE) {
    return $this->getDataFromHandler($this->configuration['core'] . '/admin/system', $reset);
  }

  /**
   * Prepares the connection to the Solr server.
   */
  protected function connect() {
    if (!$this->solr instanceof SolariumClient) {
      $config = $this->defaultConfiguration();
      $this->solr = $this->createClient($config);
    }
    return $this->solr;
  }

  /**
   * @param array $configuration
   *   Ignored in favor of the default pantheon config.
   *
   * @return object|\Solarium\Client|null
   */
  protected function createClient(array &$configuration) {
    return $this->solariumClient;
  }

  /**
   * @param string $handler
   *
   * @return mixed
   */
  protected function getStatsQuery(string $handler) {
    return json_decode(
          $this->pantheonGuzzle
            ->get(
                  $handler,
                  [
                      'query' =>
                          [
                              'stats' => 'true',
                              'wt' => 'json',
                              'accept' => 'application/json',
                              'contenttype' => 'application/json',
                              'json.nl' => 'flat',
                          ],
                      'headers' =>
                          [
                              'Content-Type' => 'application/json',
                              'Accept' => 'application/json',
                          ],
                  ]
              )
            ->getBody(),
          TRUE,
          JSON_THROW_ON_ERROR
      );
  }

}
