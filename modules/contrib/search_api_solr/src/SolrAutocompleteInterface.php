<?php

namespace Drupal\search_api_solr;

use Drupal\search_api\Query\QueryInterface;
use Drupal\search_api_autocomplete\SearchInterface;

/**
 * Defines an autocomplete interface for Solr search backend plugins.
 *
 * @deprecated These functions were moved to the autocomplete plugins. The
 *             interface will be removed in Search API Solr 4.3.0.
 */
interface SolrAutocompleteInterface {

  /**
   * Autocompletion suggestions for some user input using Terms component.
   *
   * @param \Drupal\search_api\Query\QueryInterface $query
   *   A query representing the base search, with all completely entered words
   *   in the user input so far as the search keys.
   * @param \Drupal\search_api_autocomplete\SearchInterface $search
   *   An object containing details about the search the user is on, and
   *   settings for the autocompletion. See the class documentation for details.
   *   Especially $search->getOptions() should be checked for settings, like
   *   whether to try and estimate result counts for returned suggestions.
   * @param string $incomplete_key
   *   The start of another fulltext keyword for the search, which should be
   *   completed. Might be empty, in which case all user input up to now was
   *   considered completed. Then, additional keywords for the search could be
   *   suggested.
   * @param string $user_input
   *   The complete user input for the fulltext search keywords so far.
   *
   * @return \Drupal\search_api_autocomplete\Suggestion\SuggestionInterface[]
   *   An array of autocomplete suggestions.
   *
   * @throws \Drupal\search_api\SearchApiException
   */
  public function getTermsSuggestions(QueryInterface $query, SearchInterface $search, $incomplete_key, $user_input);

  /**
   * Autocompletion suggestions for some user input using Spellcheck component.
   *
   * @param \Drupal\search_api\Query\QueryInterface $query
   *   A query representing the base search, with all completely entered words
   *   in the user input so far as the search keys.
   * @param \Drupal\search_api_autocomplete\SearchInterface $search
   *   An object containing details about the search the user is on, and
   *   settings for the autocompletion. See the class documentation for details.
   *   Especially $search->getOptions() should be checked for settings, like
   *   whether to try and estimate result counts for returned suggestions.
   * @param string $incomplete_key
   *   The start of another fulltext keyword for the search, which should be
   *   completed. Might be empty, in which case all user input up to now was
   *   considered completed. Then, additional keywords for the search could be
   *   suggested.
   * @param string $user_input
   *   The complete user input for the fulltext search keywords so far.
   *
   * @return \Drupal\search_api_autocomplete\Suggestion\SuggestionInterface[]
   *   An array of autocomplete suggestions.
   *
   * @throws \Drupal\search_api\SearchApiException
   */
  public function getSpellcheckSuggestions(QueryInterface $query, SearchInterface $search, $incomplete_key, $user_input);

  /**
   * Autocompletion suggestions for some user input using Suggester component.
   *
   * @param \Drupal\search_api\Query\QueryInterface $query
   *   A query representing the base search, with all completely entered words
   *   in the user input so far as the search keys.
   * @param \Drupal\search_api_autocomplete\SearchInterface $search
   *   An object containing details about the search the user is on, and
   *   settings for the autocompletion. See the class documentation for details.
   *   Especially $search->getOptions() should be checked for settings, like
   *   whether to try and estimate result counts for returned suggestions.
   * @param string $incomplete_key
   *   The start of another fulltext keyword for the search, which should be
   *   completed. Might be empty, in which case all user input up to now was
   *   considered completed. Then, additional keywords for the search could be
   *   suggested.
   * @param string $user_input
   *   The complete user input for the fulltext search keywords so far.
   * @param array $options
   *   (optional) An associative array of options with 'dictionary' as string,
   *   'context_filter_tags' as array of strings.
   *
   * @return \Drupal\search_api_autocomplete\Suggestion\SuggestionInterface[]
   *   An array of autocomplete suggestions.
   *
   * @throws \Drupal\search_api\SearchApiException
   */
  public function getSuggesterSuggestions(QueryInterface $query, SearchInterface $search, $incomplete_key, $user_input, array $options = []);

}
