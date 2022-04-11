<?php

namespace Drupal\search_api_solr\Controller;

use Drupal\search_api\ServerInterface;
use Drupal\search_api_solr\SolrConfigInterface;
use ZipStream\Option\Archive;

/**
 * Provides different listings of SolrFieldType.
 */
class SolrFieldTypeController extends AbstractSolrEntityController {

  /**
   * Entity type id.
   *
   * @var string
   */
  protected $entityTypeId = 'solr_field_type';

  /**
   * Provides a zip archive containing a complete Solr configuration.
   *
   * @param \Drupal\search_api\ServerInterface $search_api_server
   *   The Search API server entity.
   *
   * @return array|void
   *   A render array as expected by drupal_render().
   */
  public function getConfigZip(ServerInterface $search_api_server) {
    try {
      $archive_options = new Archive();
      $archive_options->setSendHttpHeaders(TRUE);

      @ob_clean();
      // If you are using nginx as a webserver, it will try to buffer the
      // response. We have to disable this with a custom header.
      // @see https://github.com/maennchen/ZipStream-PHP/wiki/nginx
      header('X-Accel-Buffering: no');
      $zip = $this->getListBuilder($search_api_server)->getConfigZip($archive_options);
      $zip->finish();
      @ob_end_flush();

      exit();
    }
    catch (\Exception $e) {
      watchdog_exception('search_api', $e);
      $this->messenger->addError($this->t('An error occured during the creation of the config.zip. Look at the logs for details.'));
    }

    return [];
  }

  /**
   * Disables a Solr Entity on this server.
   *
   * @param \Drupal\search_api\ServerInterface $search_api_server
   *   Search API server.
   * @param \Drupal\search_api_solr\SolrConfigInterface $solr_field_type
   *   Solr field type.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   Redirect response.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function disableOnServer(ServerInterface $search_api_server, SolrConfigInterface $solr_field_type) {
    return parent::disableOnServer($search_api_server, $solr_field_type);
  }

  /**
   * Enables a Solr Entity on this server.
   *
   * @param \Drupal\search_api\ServerInterface $search_api_server
   *   Search API server.
   * @param \Drupal\search_api_solr\SolrConfigInterface $solr_field_type
   *   Solr field type.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   Redirect response.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function enableOnServer(ServerInterface $search_api_server, SolrConfigInterface $solr_field_type) {
    return parent::enableOnServer($search_api_server, $solr_field_type);
  }

}
