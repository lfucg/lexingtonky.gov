<?php

namespace Drupal\search_api_solr\Plugin\search_api_autocomplete\suggester;

use Drupal\search_api_solr_autocomplete\Plugin\search_api_autocomplete\suggester\Spellcheck as SpellcheckOriginal;

@trigger_error('The ' . __NAMESPACE__ . '\Spellcheck is deprecated in search_api_solr:4.2.4 and is removed from search_api_solr:4.3.0. Instead use \Drupal\search_api_solr_autocomplete\Plugin\search_api_autocomplete\suggester\Spellcheck. See https://www.drupal.org/node/3254186.', E_USER_DEPRECATED);

/**
 * Provides a suggester plugin that retrieves suggestions from the server.
 *
 * The server needs to support the "search_api_autocomplete" feature for this to
 * work.
 *
 * @deprecated in search_api_solr:4.2.4 and is removed from search_api_solr:4.3.0. Use the
 *   \Drupal\search_api_solr_autocomplete\Plugin\search_api_autocomplete\suggester\Spellcheck instead
 *
 * @see https://www.drupal.org/node/3254186
 */
class Spellcheck extends SpellcheckOriginal {}
