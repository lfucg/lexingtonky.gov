<?php

namespace Drupal\search_api_solr\Event;

use ZipStream\ZipStream;

final class PostConfigSetGenerationEvent extends AbstractServerAwareEvent {

  /**
   * The zip stream.
   *
   * @var \ZipStream\ZipStream
   */
  protected $zipStream;

  /**
   * Constructs a new class instance.
   *
   * @param \ZipStream\ZipStream $zip_stream
   * @param string $lucene_match_version
   * @param string $server_id
   */
  public function __construct(ZipStream $zip_stream, string $lucene_match_version, string $server_id) {
    parent::__construct($lucene_match_version, $server_id);
    $this->zipStream = $zip_stream;
  }

  /**
   * Retrieves the files array.
   *
   * @return ZipStream
   */
  public function getZipStream(): ZipStream {
    return $this->zipStream;
  }

}
