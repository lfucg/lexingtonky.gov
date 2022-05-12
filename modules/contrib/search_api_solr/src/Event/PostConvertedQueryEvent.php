<?php

namespace Drupal\search_api_solr\Event;

/**
 * Event to be fired after the Search API query has been finally converted into a solarium query.
 */
final class PostConvertedQueryEvent extends AbstractSearchApiQuerySolariumQueryEvent {}
