<?php

namespace Drupal\search_api_solr\Event;

/**
 * Event to be fired after all solarium documents have been created for indexing.
 */
final class PostCreateIndexDocumentsEvent extends AbstractSearchApiItemsSolariumDocumentsEvent {}
