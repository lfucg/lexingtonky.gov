<?php

namespace Drupal\search_api_solr\Event;

/**
 * Event to be fired after a solarium document has been created for indexing.
 */
final class PostCreateIndexDocumentEvent extends AbstractSearchApiItemSolariumDocumentEvent {}
