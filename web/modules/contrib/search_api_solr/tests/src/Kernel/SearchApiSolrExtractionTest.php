<?php

namespace Drupal\Tests\search_api_solr\Kernel;

use Drupal\search_api\Entity\Server;

/**
 * Test tika extension based PDF extraction.
 *
 * @group search_api_solr
 */
class SearchApiSolrExtractionTest extends SolrBackendTestBase {

  /**
   * Test tika extension based PDF extraction.
   */
  public function testBackend() {
    $filepath = drupal_get_path('module', 'search_api_solr_test') . '/assets/test_extraction.pdf';
    /** @var \Drupal\search_api_solr\Plugin\search_api\backend\SearchApiSolrBackend $backend */
    $backend = Server::load($this->serverId)->getBackend();
    $content = $backend->extractContentFromFile($filepath);
    $this->assertStringContainsString('The extraction seems working!', $content);
  }

}
