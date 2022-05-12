<?php

namespace Drupal\search_api_solr\Event;

/**
 * Event to be fired before the Search API query gets finally converted into a solarium query.
 */
final class PreQueryEvent extends AbstractSearchApiQuerySolariumQueryEvent {}
