<?php

namespace Drupal\search_api_solr\Controller;

use Drupal\Component\Render\FormattableMarkup;
use Drupal\Core\Controller\ControllerBase;
use Drupal\search_api\ServerInterface;
use Drupal\search_api_solr\Event\PostConfigFilesGenerationEvent;
use Drupal\search_api_solr\Event\PostConfigSetGenerationEvent;
use Drupal\search_api_solr\Event\PostConfigSetTemplateMappingEvent;
use Drupal\search_api_solr\SearchApiSolrConflictingEntitiesException;
use Drupal\search_api_solr\SearchApiSolrException;
use Drupal\search_api_solr\SolrBackendInterface;
use Drupal\search_api_solr\Utility\Utility;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use ZipStream\Option\Archive;
use ZipStream\ZipStream;

defined('SEARCH_API_SOLR_JUMP_START_CONFIG_SET') || define('SEARCH_API_SOLR_JUMP_START_CONFIG_SET', getenv('SEARCH_API_SOLR_JUMP_START_CONFIG_SET') ?: 0);

/**
 * Provides different listings of SolrFieldType.
 */
class SolrConfigSetController extends ControllerBase {

  use BackendTrait;
  use EventDispatcherTrait;

  /**
   * @var EventDispatcherInterface
   */
  protected $eventDispatcher;

  /**
   * Provides an XML snippet containing all extra Solr field types.
   *
   * @param \Drupal\search_api\ServerInterface|null $search_api_server
   *   The Search API server entity.
   *
   * @return string
   *   XML snippet containing all extra Solr field types.
   *
   * @throws \Drupal\search_api\SearchApiException
   */
  public function getSchemaExtraTypesXml(?ServerInterface $search_api_server = NULL): string {
    /** @var \Drupal\search_api_solr\Controller\SolrFieldTypeListBuilder $list_builder */
    $list_builder = $this->getListBuilder('solr_field_type', $search_api_server);
    return $list_builder->getSchemaExtraTypesXml();
  }

  /**
   * Streams schema_extra_types.xml.
   *
   * @param \Drupal\search_api\ServerInterface $search_api_server
   *   The Search API server entity.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   The HTTP response object.
   *
   * @throws \Drupal\search_api\SearchApiException
   * @throws \Drupal\Core\Entity\EntityMalformedException
   */
  public function streamSchemaExtraTypesXml(ServerInterface $search_api_server): Response {
    try {
      return $this->streamXml('schema_extra_types.xml', $this->getSchemaExtraTypesXml($search_api_server));
    }
    catch (SearchApiSolrConflictingEntitiesException $e) {
      $this->messenger()->addError($this->t('Some enabled parts of the configuration conflict with others: :conflicts', ['@conflicts' => new FormattableMarkup($e, [])]));
    }
    return new RedirectResponse($search_api_server->toUrl('canonical')->toString());
  }

  /**
   * Provides an XML snippet containing all extra Solr fields.
   *
   * @param \Drupal\search_api\ServerInterface|null $search_api_server
   *   The Search API server entity.
   *
   * @return string
   *   XML snippet containing all extra Solr fields.
   *
   * @throws \Drupal\search_api\SearchApiException
   */
  public function getSchemaExtraFieldsXml(?ServerInterface $search_api_server = NULL): string {
    $solr_major_version = NULL;
    if ($search_api_server) {
      /** @var \Drupal\search_api_solr\SolrBackendInterface $backend */
      $backend = $search_api_server->getBackend();
      $solr_major_version = $backend->getSolrConnector()->getSolrMajorVersion();
    }

    /** @var \Drupal\search_api_solr\Controller\SolrFieldTypeListBuilder $list_builder */
    $list_builder = $this->getListBuilder('solr_field_type', $search_api_server);
    return $list_builder->getSchemaExtraFieldsXml($solr_major_version);
  }

  /**
   * Streams schema_extra_fields.xml.
   *
   * @param \Drupal\search_api\ServerInterface $search_api_server
   *   The Search API server entity.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   The HTTP response object.
   *
   * @throws \Drupal\search_api\SearchApiException
   */
  public function streamSchemaExtraFieldsXml(ServerInterface $search_api_server): Response {
    try {
      return $this->streamXml('schema_extra_fields.xml', $this->getSchemaExtraFieldsXml($search_api_server));
    }
    catch (SearchApiSolrConflictingEntitiesException $e) {
      $this->messenger()->addError($this->t('Some enabled parts of the configuration conflict with others: @conflicts', ['@conflicts' => new FormattableMarkup($e, [])]));
    }
    return new RedirectResponse($search_api_server->toUrl('canonical')->toString());
  }

  /**
   * Provides an XML snippet containing all extra solrconfig.
   *
   * @param \Drupal\search_api\ServerInterface|null $search_api_server
   *   The Search API server entity.
   *
   * @return string
   *   XML snippet containing all extra solrconfig.
   *
   * @throws \Drupal\search_api\SearchApiException
   */
  public function getSolrconfigExtraXml(?ServerInterface $search_api_server = NULL): string {
    /** @var \Drupal\search_api_solr\Controller\SolrFieldTypeListBuilder $solr_field_type_list_builder */
    $solr_field_type_list_builder = $this->getListBuilder('solr_field_type', $search_api_server);

    /** @var \Drupal\search_api_solr\Controller\SolrRequestHandlerListBuilder $solr_request_handler_list_builder */
    $solr_request_handler_list_builder = $this->getListBuilder('solr_request_handler', $search_api_server);

    return $solr_field_type_list_builder->getSolrconfigExtraXml() . $solr_request_handler_list_builder->getXml();
  }

  /**
   * Streams solrconfig_extra.xml.
   *
   * @param \Drupal\search_api\ServerInterface $search_api_server
   *   The Search API server entity.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   The HTTP response object.
   *
   * @throws \Drupal\search_api\SearchApiException
   */
  public function streamSolrconfigExtraXml(ServerInterface $search_api_server): Response {
    try {
      return $this->streamXml('solrconfig_extra.xml', $this->getSolrconfigExtraXml($search_api_server));
    }
    catch (SearchApiSolrConflictingEntitiesException $e) {
      $this->messenger()->addError($this->t('Some enabled parts of the configuration conflict with others: @conflicts', ['@conflicts' => new FormattableMarkup($e, [])]));
    }
    return new RedirectResponse($search_api_server->toUrl('canonical')->toString());
  }

  /**
   * Provides an XML snippet containing all index settings as XML.
   *
   * @param \Drupal\search_api\ServerInterface|null $search_api_server
   *   The Search API server entity.
   *
   * @return string
   *   XML snippet containing all index settings.
   */
  public function getSolrconfigIndexXml(?ServerInterface $search_api_server = NULL): string {
    // Reserved for future internal use.
    return '';
  }

  /**
   * Provides an XML snippet containing all query cache settings as XML.
   *
   * @param \Drupal\search_api\ServerInterface|null $search_api_server
   *   The Search API server entity.
   *
   * @return string
   *   XML snippet containing all query cache settings.
   *
   * @throws \Drupal\search_api\SearchApiException
   */
  public function getSolrconfigQueryXml(?ServerInterface $search_api_server = NULL): string {
    /** @var \Drupal\search_api_solr\Controller\SolrCacheListBuilder $list_builder */
    $list_builder = $this->getListBuilder('solr_cache', $search_api_server);
    return $list_builder->getXml();
  }

  /**
   * Streams solrconfig_query.xml.
   *
   * @param \Drupal\search_api\ServerInterface $search_api_server
   *   The Search API server entity.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   The HTTP response object.
   *
   * @throws \Drupal\search_api\SearchApiException
   */
  public function streamSolrconfigQueryXml(ServerInterface $search_api_server): Response {
    try {
      return $this->streamXml('solrconfig_query.xml', $this->getSolrconfigQueryXml($search_api_server));
    }
    catch (SearchApiSolrConflictingEntitiesException $e) {
      $this->messenger()->addError($this->t('Some enabled parts of the configuration conflict with others: @conflicts', ['@conflicts' => new FormattableMarkup($e, [])]));
    }
    return new RedirectResponse($search_api_server->toUrl('canonical')->toString());
  }

  /**
   * Provides an XML snippet containing all request dispatcher settings as XML.
   *
   * @param \Drupal\search_api\ServerInterface|null $search_api_server
   *   The Search API server entity.
   *
   * @return string
   *   The XML snippet containing all request dispatcher settings.
   *
   * @throws \Drupal\search_api\SearchApiException
   */
  public function getSolrconfigRequestDispatcherXml(?ServerInterface $search_api_server = NULL): string {
    /** @var \Drupal\search_api_solr\Controller\SolrRequestDispatcherListBuilder $list_builder */
    $list_builder = $this->getListBuilder('solr_request_dispatcher', $search_api_server);
    return $list_builder->getXml();
  }

  /**
   * Streams solrconfig_requestdispatcher.xml.
   *
   * @param \Drupal\search_api\ServerInterface $search_api_server
   *   The Search API server entity.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   The HTTP response object.
   *
   * @throws \Drupal\search_api\SearchApiException
   */
  public function streamSolrconfigRequestDispatcherXml(ServerInterface $search_api_server): Response {
    try {
      return $this->streamXml('solrconfig_requestdispatcher.xml', $this->getSolrconfigRequestDispatcherXml($search_api_server));
    }
    catch (SearchApiSolrConflictingEntitiesException $e) {
      $this->messenger()->addError($this->t('Some enabled parts of the configuration conflict with others: @conflicts', ['@conflicts' => new FormattableMarkup($e, [])]));
    }
    return new RedirectResponse($search_api_server->toUrl('canonical')->toString());
  }

  /**
   * Returns the configuration files names and content.
   *
   * @return array
   *   An associative array of files names and content.
   *
   * @throws \Drupal\search_api\SearchApiException
   * @throws \Drupal\search_api_solr\SearchApiSolrException
   */
  public function getConfigFiles(): array {
    /** @var \Drupal\search_api_solr\SolrBackendInterface $backend */
    $backend = $this->getBackend();
    $connector = $backend->getSolrConnector();
    $solr_branch = $real_solr_branch = $connector->getSolrBranch($this->assumedMinimumVersion);
    $solr_major_version = $connector->getSolrMajorVersion($this->assumedMinimumVersion);

    $template_path = drupal_get_path('module', 'search_api_solr') . '/solr-conf-templates/';
    $solr_configset_template_mapping = [
      '6.x' => $template_path . '6.x',
      '7.x' => $template_path . '7.x',
      '8.x' => $template_path . '8.x',
    ];

    $this->moduleHandler()->alterDeprecated('hook_search_api_solr_configset_template_mapping_alter is deprecated will be removed in Search API Solr 4.3.0. Handle the PostConfigSetTemplateMappingEvent instead.','search_api_solr_configset_template_mapping', $solr_configset_template_mapping);
    $event = new PostConfigSetTemplateMappingEvent($solr_configset_template_mapping);
    $this->eventDispatcher()->dispatch($event);
    $solr_configset_template_mapping = $event->getConfigSetTemplateMapping();

    $search_api_solr_conf_path = $solr_configset_template_mapping[$solr_branch];
    $solrcore_properties_file = $search_api_solr_conf_path . '/solrcore.properties';
    if (file_exists($solrcore_properties_file) && is_readable($solrcore_properties_file)) {
      $solrcore_properties = parse_ini_file($solrcore_properties_file, FALSE, INI_SCANNER_RAW);
    }
    else {
      throw new SearchApiSolrException('solrcore.properties template could not be parsed.');
    }

    $files = [
      'schema_extra_types.xml' => $this->getSchemaExtraTypesXml(),
      'schema_extra_fields.xml' => $this->getSchemaExtraFieldsXml($backend->getServer()),
      'solrconfig_extra.xml' => $this->getSolrconfigExtraXml(),
      'solrconfig_index.xml' => $this->getSolrconfigIndexXml(),
    ];

    if (!$backend->isNonDrupalOrOutdatedConfigSetAllowed() && (empty($files['schema_extra_types.xml']) || empty($files['schema_extra_fields.xml']))) {
      throw new SearchApiSolrException(sprintf('The configs of the essential Solr field types are missing or broken for server "%s".', $backend->getServer()->id()));
    }

    if (version_compare($solr_major_version, '7', '>=')) {
      $files['solrconfig_query.xml'] = $this->getSolrconfigQueryXml();
      $files['solrconfig_requestdispatcher.xml'] = $this->getSolrconfigRequestDispatcherXml();
    }

    // Add language specific text files.
    $list_builder = $this->getListBuilder('solr_field_type');
    $solr_field_types = $list_builder->getEnabledEntities();

    /** @var \Drupal\search_api_solr\SolrFieldTypeInterface $solr_field_type */
    foreach ($solr_field_types as $solr_field_type) {
      $text_files = $solr_field_type->getTextFiles();
      foreach ($text_files as $text_file_name => $text_file) {
        $text_file_name = Utility::completeTextFileName($text_file_name, $solr_field_type);
        $files[$text_file_name] = $text_file;
        $solrcore_properties['solr.replication.confFiles'] .= ',' . $text_file_name;
      }
    }

    $solrcore_properties['solr.luceneMatchVersion'] = $connector->getLuceneMatchVersion($this->assumedMinimumVersion ?: '');
    if (!$connector->isCloud()) {
      // @todo
      // $solrcore_properties['solr.replication.masterUrl']
      $solrcore_properties_string = '';
      foreach ($solrcore_properties as $property => $value) {
        $solrcore_properties_string .= $property . '=' . $value . "\n";
      }
      $files['solrcore.properties'] = $solrcore_properties_string;
    }

    // Now add all remaining static files from the conf dir that have not been
    // generated dynamically above.
    foreach (scandir($search_api_solr_conf_path) as $file) {
      if (strpos($file, '.') !== 0 && !array_key_exists($file, $files)) {
        $file_path = $search_api_solr_conf_path . '/' . $file;
        if (file_exists($file_path) && is_readable($file_path)) {
          $files[$file] = str_replace(
            ['SEARCH_API_SOLR_SCHEMA_VERSION', 'SEARCH_API_SOLR_BRANCH', 'SEARCH_API_SOLR_JUMP_START_CONFIG_SET'],
            [SolrBackendInterface::SEARCH_API_SOLR_SCHEMA_VERSION, $real_solr_branch, SEARCH_API_SOLR_JUMP_START_CONFIG_SET],
            file_get_contents($search_api_solr_conf_path . '/' . $file)
          );
        }
        else {
          throw new SearchApiSolrException(sprintf('%s template is not readable.', $file));
        }
      }
    }

    if ($connector->isCloud() && isset($files['solrconfig.xml'])) {
      // solrcore.properties won’t work in SolrCloud mode (it is not read from
      // ZooKeeper). Therefore we go for a more specific fallback to keep the
      // possibility to set the property as parameter of the virtual machine.
      // @see https://lucene.apache.org/solr/guide/8_6/configuring-solrconfig-xml.html
      $files['solrconfig.xml'] = preg_replace('/solr.luceneMatchVersion:LUCENE_\d+/', 'solr.luceneMatchVersion:' . $solrcore_properties['solr.luceneMatchVersion'], $files['solrconfig.xml']);
      unset($files['solrcore.properties']);
    }

    $connector->alterConfigFiles($files, $solrcore_properties['solr.luceneMatchVersion'], $this->serverId);
    $this->moduleHandler()->alterDeprecated('hook_search_api_solr_config_files_alter is deprecated will be removed in Search API Solr 4.3.0. Handle the PostConfigFilesGenerationEvent instead.','search_api_solr_config_files', $files, $solrcore_properties['solr.luceneMatchVersion'], $this->serverId);
    $event = new PostConfigFilesGenerationEvent($files, $solrcore_properties['solr.luceneMatchVersion'], $this->serverId);
    $this->eventDispatcher()->dispatch($event);

    return $event->getConfigFiles();
  }

  /**
   * Returns a ZipStream of all configuration files.
   *
   * @param \ZipStream\Option\Archive $archive_options
   *   Archive options.
   *
   * @return \ZipStream\ZipStream
   *   The ZipStream that contains all configuration files.
   *
   * @throws \Drupal\search_api\SearchApiException
   * @throws \ZipStream\Exception\FileNotFoundException
   * @throws \ZipStream\Exception\FileNotReadableException
   */
  public function getConfigZip(Archive $archive_options): ZipStream {
    /** @var \Drupal\search_api_solr\SolrBackendInterface $backend */
    $backend = $this->getBackend();
    $connector = $backend->getSolrConnector();
    $solr_branch = $connector->getSolrBranch($this->assumedMinimumVersion);
    $lucene_match_version = $connector->getLuceneMatchVersion($this->assumedMinimumVersion ?: '');

    $zip = new ZipStream('solr_' . $solr_branch . '_config.zip', $archive_options);

    $files = $this->getConfigFiles();

    foreach ($files as $name => $content) {
      $zip->addFile($name, $content);
    }

    $connector->alterConfigZip($zip, $lucene_match_version, $this->serverId);
    $this->moduleHandler->alterDeprecated('hook_search_api_solr_config_zip_alter is deprecated will be removed in Search API Solr 4.3.0. Handle the PostConfigSetGenerationEvent instead.','search_api_solr_config_zip', $zip, $lucene_match_version, $this->serverId);
    $event = new PostConfigSetGenerationEvent($zip, $lucene_match_version, $this->serverId);
    $this->eventDispatcher()->dispatch($event);

    return $event->getZipStream();
  }

  /**
   * Streams a zip archive containing a complete Solr configuration.
   *
   * @param \Drupal\search_api\ServerInterface $search_api_server
   *   The Search API server entity.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   The HTTP response object.
   *
   * @throws \Drupal\search_api\SearchApiException
   */
  public function streamConfigZip(ServerInterface $search_api_server): Response {
    $this->setServer($search_api_server);

    try {
      $archive_options = new Archive();
      $archive_options->setSendHttpHeaders(TRUE);

      @ob_clean();
      // If you are using nginx as a webserver, it will try to buffer the
      // response. We have to disable this with a custom header.
      // @see https://github.com/maennchen/ZipStream-PHP/wiki/nginx
      header('X-Accel-Buffering: no');
      $zip = $this->getConfigZip($archive_options);
      $zip->finish();
      @ob_end_flush();

      exit();
    }
    catch (SearchApiSolrConflictingEntitiesException $e) {
      $this->messenger()->addError($this->t('Some enabled parts of the configuration conflict with others: @conflicts', ['@conflicts' => new FormattableMarkup($e, [])]));
    }
    catch (\Exception $e) {
      watchdog_exception('search_api', $e);
      $this->messenger()->addError($this->t('An error occurred during the creation of the config.zip. Look at the logs for details.'));
    }

    return new RedirectResponse($search_api_server->toUrl('canonical')->toString());
  }

  /**
   * Streams a zip archive containing a complete Solr configuration currently in use.
   *
   * @param \Drupal\search_api\ServerInterface $search_api_server
   *   The Search API server entity.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   The HTTP response object.
   */
  public function streamCurrentConfigZip(ServerInterface $search_api_server): Response {
    try {
      /** @var \Drupal\search_api_solr\SolrBackendInterface $backend */
      $backend = $search_api_server->getBackend();

      $archive_options = new Archive();
      $archive_options->setSendHttpHeaders(TRUE);

      @ob_clean();
      // If you are using nginx as a webserver, it will try to buffer the
      // response. We have to disable this with a custom header.
      // @see https://github.com/maennchen/ZipStream-PHP/wiki/nginx
      header('X-Accel-Buffering: no');

      $zip = new ZipStream('solr_current_config.zip', $archive_options);

      /** @var \Drupal\search_api_solr\SolrBackendInterface $backend */
      $backend = $search_api_server->getBackend();

      $files_list = Utility::getServerFiles($search_api_server);

      foreach ($files_list as $file_name => $file_info) {
        $content = '';
        if ($file_info['size'] > 0) {
          $file_data = $backend->getSolrConnector()->getFile($file_name);
          $content = $file_data->getBody();
        }

        $zip->addFile($file_name, $content);
      }

      $zip->finish();
      @ob_end_flush();

      exit();
    }
    catch (\Exception $e) {
      watchdog_exception('search_api', $e);
      $this->messenger()->addError($this->t('An error occurred during the creation of the config.zip. Look at the logs for details.'));
    }

    return new RedirectResponse($search_api_server->toUrl('canonical')->toString());
  }

  /**
   * Provides an XML snippet containing all query cache settings as XML.
   *
   * @param \Drupal\search_api_solr\Controller\string $file_name
   *   The file name.
   * @param \Drupal\search_api_solr\Controller\string $xml
   *   The XML.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   The HTTP response object.
   */
  protected function streamXml(string $file_name, string $xml): Response {
    return new Response(
      $xml,
      200,
      [
        'Content-Type' => 'application/xml',
        'Content-Disposition' => 'attachment; filename=' . $file_name,
      ]
    );
  }

  /**
   * Returns a ListBuilder.
   *
   * @param string $entity_type_id
   *   Entity type id.
   * @param \Drupal\search_api\ServerInterface|null $search_api_server
   *   Search API Server.
   *
   * @return \Drupal\search_api_solr\Controller\AbstractSolrEntityListBuilder
   *   A ListBuilder.
   *
   * @throws \Drupal\search_api\SearchApiException
   */
  protected function getListBuilder(string $entity_type_id, ?ServerInterface $search_api_server = NULL): AbstractSolrEntityListBuilder {
    /** @var \Drupal\search_api_solr\Controller\AbstractSolrEntityListBuilder $list_builder */
    $list_builder = $this->entityTypeManager()->getListBuilder($entity_type_id);
    if ($search_api_server) {
      $list_builder->setServer($search_api_server);
    }
    else {
      $list_builder->setBackend($this->getBackend());
    }
    return $list_builder;
  }

}
