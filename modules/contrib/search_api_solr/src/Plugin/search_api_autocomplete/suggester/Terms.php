<?php

namespace Drupal\search_api_solr\Plugin\search_api_autocomplete\suggester;

use Drupal\search_api_solr_autocomplete\Plugin\search_api_autocomplete\suggester\Terms as TermsOriginal;

@trigger_error('The ' . __NAMESPACE__ . '\Terms is deprecated in search_api_solr:4.2.4 and is removed from search_api_solr:4.3.0. Instead use \Drupal\search_api_solr_autocomplete\Plugin\search_api_autocomplete\suggester\Terms. See https://www.drupal.org/node/3254186.', E_USER_DEPRECATED);

/**
 * Provides a suggester that retrieves suggestions from Solr's Terms component.
 *
 * @deprecated in search_api_solr:4.2.4 and is removed from search_api_solr:4.3.0. Use the
 *    \Drupal\search_api_solr_autocomplete\Plugin\search_api_autocomplete\suggester\Terms instead
 *
 * @see https://www.drupal.org/node/3254186
 */
class Terms extends TermsOriginal {}
