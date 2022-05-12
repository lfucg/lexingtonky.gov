<?php

/**
 * @file
 * Hooks provided by the Search API Solr search module.
 */

use Drupal\search_api_solr\Event\PostConfigFilesGenerationEvent;
use Drupal\search_api_solr\Event\PostConfigSetGenerationEvent;
use Drupal\search_api_solr\Event\PostConvertedQueryEvent;
use Drupal\search_api_solr\Event\PostCreateIndexDocumentsEvent;
use Drupal\search_api_solr\Event\PostExtractResultsEvent;
use Drupal\search_api_solr\Event\PostFieldMappingEvent;
use Drupal\search_api_solr\Event\PreAutocompleteTermsQueryEvent;
use Drupal\search_api_solr\Event\PreIndexFinalizationEvent;
use Drupal\search_api_solr\Event\PreQueryEvent;
use Drupal\search_api_solr_autocomplete\Event\PreSpellcheckQueryEvent;
use Drupal\search_api_solr_autocomplete\Event\PreSuggesterQueryEvent;

/**
 * @addtogroup hooks
 * @{
 */

/**
 * Lets modules alter the Solarium select query before executing it.
 *
 * After this hook, the select query will be finally converted into an
 * expression that will be processed by the lucene query parser. Therefore you
 * can't modify the 'q' parameter here, because it gets overwritten by that
 * conversion. If you need to modify the 'q' parameter you should implement an
 * event listener instead of this hook that handles the solarium events (our
 * connector injects the drupal event handler into solarium) or implement
 * hook_search_api_solr_converted_query() instead. If you want to force a
 * different parser like edismax you must set the 'defType' parameter
 * accordingly.
 *
 * @param \Solarium\Core\Query\QueryInterface $solarium_query
 *   The Solarium query object, as generated from the Search API query.
 * @param \Drupal\search_api\Query\QueryInterface $query
 *   The Search API query object representing the executed search query.
 *
 * @deprecated This function will be removed in Search API Solr 4.3.0.
 *             Handle the PreQueryEvent instead.
 */
function hook_search_api_solr_query_alter(\Solarium\Core\Query\QueryInterface $solarium_query, \Drupal\search_api\Query\QueryInterface $query) {
  // To get a list of solrium events:
  // @see http://solarium.readthedocs.io/en/stable/customizing-solarium/#plugin-system
  // If the Search API query has a 'my_custom_boost' option, boost German
  // results.
  if ($query->getOption('my_custom_boost')) {
    if ($boosts = $query->getOption('solr_document_boost_factors', [])) {
      $boosts['search_api_language'] = sprintf('if(eq(%s,"%s"),%2F,0.0)', \Drupal\search_api_solr\SolrBackendInterface::FIELD_PLACEHOLDER, 'de', 1.2);
      $query->setOption('solr_document_boost_factors', $boosts);
    }
  }
}

/**
 * Lets modules alter the terms autocomplete query before executing it.
 *
 * @param \Drupal\search_api\Query\QueryInterface $query
 *   The Search API query object representing the executed search query.
 *
 * @deprecated This function will be removed in Search API Solr 4.3.0.
 *             Handle the PreAutocompleteTermsQueryEvent instead.
 */
function hook_search_api_solr_terms_autocomplete_query_alter(\Drupal\search_api\Query\QueryInterface $query) {
  // If the Search API query has a 'terms' component, set a custom option.
  $query->setOption('solr_param_code', 'custom-value');
}

/**
 * Lets modules alter the spellcheck autocomplete query before executing it.
 *
 * @param \Drupal\search_api_solr\Solarium\Autocomplete\Query $solarium_query
 *   The Solarium query object, as generated from the Search API query.
 * @param \Drupal\search_api\Query\QueryInterface $query
 *   The Search API query object representing the executed search query.
 *
 * @deprecated This function will be removed in Search API Solr 4.3.0.
 *             Handle the PreSpellcheckQueryEvent instead.
 */
function hook_search_api_solr_spellcheck_autocomplete_query_alter(\Drupal\search_api_solr\Solarium\Autocomplete\Query $solarium_query, \Drupal\search_api\Query\QueryInterface $query) {
  // If the Search API query has a 'spellcheck' component, set a custom
  // dictionary.
  $solarium_query->getSpellcheck()->setDictionary('custom');
}

/**
 * Lets modules alter the suggester autocomplete query before executing it.
 *
 * @param \Drupal\search_api_solr\Solarium\Autocomplete\Query $solarium_query
 *   The Solarium query object, as generated from the Search API query.
 * @param \Drupal\search_api\Query\QueryInterface $query
 *   The Search API query object representing the executed search query.
 *
 * @deprecated This function will be removed in Search API Solr 4.3.0.
 *             Handle the PreSuggesterQueryEvent instead.
 */
function hook_search_api_solr_suggester_autocomplete_query_alter(\Drupal\search_api_solr\Solarium\Autocomplete\Query $solarium_query, \Drupal\search_api\Query\QueryInterface $query) {
  // If the Search API query has a 'suggester' component, set a custom
  // dictionary.
  $solarium_query->getSuggester()->setDictionary('custom');
}

/**
 * Lets modules alter the converted Solarium select query before executing it.
 *
 * This hook is called after the select query is finally converted into an
 * expression that meets the requirements of the trageted query parser. Using
 *  this hook you can carefully modify the 'q' parameter here, in oposite to
 * hook_search_api_solr_query_alter().
 *
 * @param \Solarium\Core\Query\QueryInterface $solarium_query
 *   The Solarium query object, as generated from the Search API query.
 * @param \Drupal\search_api\Query\QueryInterface $query
 *   The Search API query object representing the executed search query.
 *
 * @deprecated This function will be removed in Search API Solr 4.3.0.
 *             Handle the PostConvertedQueryEvent instead.
 */
function hook_search_api_solr_converted_query_alter(\Solarium\Core\Query\QueryInterface $solarium_query, \Drupal\search_api\Query\QueryInterface $query) {
  // If the Search API query has a 'I_know_what_I_am_doing' option set to
  // 'really!', overwrite the 'q' parameter, query handler and add some boost
  // queries.
  if ($query->getOption('I_know_what_I_am_doing') === 'really!') {
    // $solr_field_names maps search_api field names to real field names in
    // the Solr index.
    $solr_field_names = $query->getIndex()->getServerInstance()->getBackend()->getSolrFieldNames($query->getIndex());

    $solarium_query->setQuery($solr_field_names['title'] . ':' . $solarium_query->getHelper()->escapePhrase('foo') . '^11.0');
  }
}

/**
 * Change the way the index's field names are mapped to Solr field names.
 *
 * @param \Drupal\search_api\IndexInterface $index
 *   The index whose field mappings are altered.
 * @param array $fields
 *   An associative array containing the index field names mapped to their Solr
 *   counterparts. The special fields 'search_api_id' and 'search_api_relevance'
 *   are also included.
 * @param string $language_id
 *   The language ID that applies for this field mapping.
 *
 * @deprecated This function will be removed in Search API Solr 4.3.0.
 *             Handle the PostFieldMappingEvent instead.
 */
function hook_search_api_solr_field_mapping_alter(\Drupal\search_api\IndexInterface $index, array &$fields, string $language_id) {
  $fields['fieldname'] = 'ss_fieldname';
}

/**
 * Alter Solr documents before they are sent to Solr for indexing.
 *
 * @param \Solarium\QueryType\Update\Query\Document[] $documents
 *   An array of \Solarium\QueryType\Update\Query\Document\Document objects
 *   ready to be indexed, generated from $items array.
 * @param \Drupal\search_api\IndexInterface $index
 *   The search index for which items are being indexed.
 * @param \Drupal\search_api\Item\ItemInterface[] $items
 *   An array of items to be indexed, keyed by their item IDs.
 *
 * @deprecated This function will be removed in Search API Solr 4.3.0.
 *             Handle the PostCreateIndexDocumentsEvent instead.
 */
function hook_search_api_solr_documents_alter(array &$documents, \Drupal\search_api\IndexInterface $index, array $items) {
  // Adds a "foo" field with value "bar" to all documents.
  foreach ($documents as $document) {
    $document->setField('foo', 'bar');
  }
}

/**
 * Lets modules alter the search results returned from a Solr search.
 *
 * @param \Drupal\search_api\Query\ResultSetInterface $result_set
 *   The results array that will be returned for the search.
 * @param \Drupal\search_api\Query\QueryInterface $query
 *   The SearchApiQueryInterface object representing the executed search query.
 * @param \Solarium\QueryType\Select\Result\Result $result
 *   The Solarium result object.
 *
 * @deprecated This function will be removed in Search API Solr 4.3.0.
 *             Handle the PostExtractResultsEvent instead.
 */
function hook_search_api_solr_search_results_alter(\Drupal\search_api\Query\ResultSetInterface $result_set, \Drupal\search_api\Query\QueryInterface $query, \Solarium\QueryType\Select\Result\Result $result) {
  $result_data = $result->getData();
  if (isset($result_data['facet_counts']['facet_fields']['custom_field'])) {
    // Do something with $result_set.
  }
}

/**
 * Provide Solr dynamic fields as Search API data types.
 *
 * This serves as a placeholder for documenting additional keys for
 * hook_search_api_data_type_info() which are recognized by this module to
 * automatically support dynamic field types from the schema.
 *
 * @return array
 *   In addition to the keys for the individual types that are defined by
 *   hook_search_api_data_type_info(), the following keys are regonized:
 *   - prefix: The Solr field name prefix to use for this type. Should match
 *     two existing dynamic fields definitions with names "{PREFIX}s_*" and
 *     "{PREFIX}m_*".
 *
 * @see hook_search_api_data_type_info()
 */
function search_api_solr_hook_search_api_data_type_info() {
  return [
    // You can use any identifier you want here, but it makes sense to use the
    // field type name from schema.xml.
    'edge_n2_kw_text' => [
      // Stock hook_search_api_data_type_info() info:
      'name' => t('Fulltext (w/ partial matching)'),
      'fallback' => 'text',
      // Dynamic field with name="tes_*" and name="tem_*".
      'prefix' => 'te',
    ],
    'tlong' => [
      // Stock hook_search_api_data_type_info() info:
      'name' => t('TrieLong'),
      'fallback' => 'integer',
      // Dynamic fields with name="its_*" and name="itm_*".
      'prefix' => 'it',
    ],
  ];
}

/**
 * Apply any finalization commands to a solr index before the first search.
 *
 * This hook will be called every time any item within the index was updated or
 * deleted. Not on every modification but before the first search happens on an
 * updated index. This could be useful to apply late modifications to the items
 * themselves within Solr which is much faster.
 *
 * @param \Drupal\search_api\IndexInterface $index
 *   The search index.
 *
 * @deprecated This function will be removed in Search API Solr 4.3.0.
 *             Handle the PreIndexFinalizationEvent instead.
 */
function hook_search_api_solr_finalize_index(\Drupal\search_api\IndexInterface $index) {

}

/**
 * Alter the newly assembled Solr configuration files.
 *
 * @param string[] $files
 *   Array of config files keyed by file names.
 * @param string $lucene_match_version
 *   Lucene (Solr) minor version string.
 * @param string $server_id
 *   Optional Search API server id. Will be set in most cases but might be
 *   empty when the config generation is triggered via UI or drush.
 *
 * @deprecated This function will be removed in Search API Solr 4.3.0.
 *             Handle the PostConfigFilesGenerationEvent instead.
 */
function hook_search_api_solr_config_files_alter(array &$files, string $lucene_match_version, string $server_id = '') {
  $files['solrconfig_extra.xml'] .= "<!-- Append additional stuff -->\n";
  // If you want to modify the existing XML files we recommend to use PHP's DOM
  // API.
}

/**
 * Alter the zip archive of newly assembled Solr configuration files.
 *
 * @param \ZipStream\ZipStream $files
 *   Zip archive.
 * @param string $lucene_match_version
 *   Lucene (Solr) minor version string.
 * @param string $server_id
 *   Optional Search API server id. Will be set in most cases but might be
 *   empty when the config generation is triggered via UI or drush.
 *
 * @deprecated This function will be removed in Search API Solr 4.3.0.
 *             Handle the PostConfigSetGenerationEvent instead.
 */
function hook_search_api_solr_config_zip_alter(\ZipStream\ZipStream $zip, string $lucene_match_version, string $server_id = '') {
}

/**
 * @} End of "addtogroup hooks".
 */
