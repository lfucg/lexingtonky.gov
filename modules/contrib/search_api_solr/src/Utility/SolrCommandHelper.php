<?php

namespace Drupal\search_api_solr\Utility;

use Drupal\search_api\ServerInterface;
use Drupal\search_api_solr\Controller\SolrConfigSetController;
use Drupal\search_api_solr\SearchApiSolrException;
use Drupal\search_api_solr\SolrBackendInterface;
use ZipStream\Option\Archive;
use Drupal\search_api\Utility\CommandHelper;

/**
 * Provides functionality to be used by CLI tools.
 */
class SolrCommandHelper extends CommandHelper {

  /**
   * Re-install all Solr Field Types from their yml files.
   */
  public function reinstallFieldtypesCommand() {
    search_api_solr_install_missing_field_types();
  }

  /**
   * Gets the config for a Solr search server.
   *
   * @param string $server_id
   *   The ID of the server.
   * @param string $file_name
   *   The file name of the config zip that should be created.
   * @param string $solr_version
   *   The targeted Solr version.
   *
   * @throws \Drupal\search_api\SearchApiException
   * @throws \ZipStream\Exception\FileNotFoundException
   * @throws \ZipStream\Exception\FileNotReadableException
   * @throws \ZipStream\Exception\OverflowException
   */
  public function getServerConfigCommand($server_id, $file_name = NULL, $solr_version = NULL) {
    $server = $this->getServer($server_id);

    if ($solr_version) {
      $config = $server->getBackendConfig();
      // Temporarily switch the Solr version but don't save!
      $config['connector_config']['solr_version'] = $solr_version;
      $server->setBackendConfig($config);
    }
    $solr_configset_controller = new SolrConfigSetController();
    $solr_configset_controller->setServer($server);

    $archive_options = new Archive();
    $stream = FALSE;
    if ($file_name !== NULL) {
      // If no filename is provided, output stream is standard output.
      $stream = fopen($file_name, 'w+b');
      $archive_options->setOutputStream($stream);
    }

    $zip = $solr_configset_controller->getConfigZip($archive_options);
    $zip->finish();

    if ($stream) {
      fclose($stream);
    }
  }

  /**
   * Finalizes one ore more indexes.
   *
   * @param string[]|null $indexIds
   *   (optional) An array of index IDs, or NULL if we should finalize all
   *   enabled indexes.
   * @param bool $force
   *   (optional) Force the finalization, even if the index isn't "dirty".
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   * @throws \Drupal\search_api\SearchApiException
   * @throws \Drupal\search_api_solr\SearchApiSolrException
   */
  public function finalizeIndexCommand(array $indexIds = NULL, $force = FALSE) {
    $servers = search_api_solr_get_servers();

    if ($force) {
      // It's important to mark all indexes as "dirty" before the first
      // finalization runs because there might be dependencies between the
      // indexes. Therefor we do the loop two times.
      foreach ($servers as $server) {
        foreach ($server->getIndexes() as $index) {
          if ($index->status() && !$index->isReadOnly() && (!$indexIds || in_array($index->id(), $indexIds))) {
            \Drupal::state()->set('search_api_solr.' . $index->id() . '.last_update', \Drupal::time()->getRequestTime());
          }
        }
      }
    }

    foreach ($servers as $server) {
      /** @var \Drupal\search_api_solr\SolrBackendInterface $backend */
      $backend = $server->getBackend();
      foreach ($server->getIndexes() as $index) {
        if ($index->status() && !$index->isReadOnly() && (!$indexIds || in_array($index->id(), $indexIds))) {
          $backend->finalizeIndex($index);
        }
      }
    }
  }

  /**
   * Gets search server.
   *
   * @param string $server_id
   *   The ID of the server.
   *
   * @throws \Drupal\search_api\SearchApiException
   */
  protected function getServer(string $server_id): ServerInterface {
    $servers = $this->loadServers([$server_id]);
    $server = reset($servers);
    if (!$server) {
      throw new SearchApiSolrException(sprintf('Unknown server %s', $server_id));
    }
    if (!($server->getBackend() instanceof SolrBackendInterface)) {
      throw new SearchApiSolrException(sprintf('Server %s is not a Solr server', $server->label()));
    }

    return $server;
  }

  /**
   * @param \Drupal\search_api\ServerInterface $server
   *
   * @throws \Drupal\search_api\SearchApiException
   */
  protected function reindex(ServerInterface $server): void {
    foreach($server->getIndexes() as $index) {
      if ($index->status() && !$index->isReadOnly()) {
        $index->reindex();
      }
    }
  }
}
