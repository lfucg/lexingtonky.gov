<?php

namespace Drupal\search_api_solr\Event;

use Drupal\Component\EventDispatcher\Event;

abstract class AbstractServerAwareEvent extends Event {

  /**
   * @var string
   */
  protected $luceneMatchVersion;

  /**
   * @var string
   */
  protected $serverId;

  /**
   * Constructs a new class instance.
   *
   * @param string $lucene_match_version
   * @param string $server_id
   */
  public function __construct(string $lucene_match_version, string $server_id) {
    $this->luceneMatchVersion = $lucene_match_version;
    $this->serverId = $server_id;
  }

  /**
   * Retrieves the lucene match version.
   */
  public function getLuceneMatchVersion(): string {
    return $this->luceneMatchVersion;
  }

  /**
   * Retrieves the server ID.
   */
  public function getServerId(): string {
    return $this->serverId;
  }
}
