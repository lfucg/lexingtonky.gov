<?php

namespace Drupal\search_api_solr\Plugin\search_api_autocomplete\suggester;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Plugin\PluginFormInterface;
use Drupal\search_api\Plugin\PluginFormTrait;
use Drupal\search_api\Query\QueryInterface;
use Drupal\search_api_autocomplete\SearchInterface;
use Drupal\search_api_autocomplete\Suggester\SuggesterPluginBase;
use Drupal\search_api_solr\Utility\Utility;

/**
 * Provides a suggester plugin that retrieves suggestions from the server.
 *
 * The server needs to support the "search_api_autocomplete" feature for this to
 * work.
 *
 * @SearchApiAutocompleteSuggester(
 *   id = "search_api_solr_suggester",
 *   label = @Translation("Solr Suggester"),
 *   description = @Translation("Suggest complete phrases for the entered string based on Solr's suggest component."),
 * )
 */
class Suggester extends SuggesterPluginBase implements PluginFormInterface {

  use PluginFormTrait;
  use BackendTrait;

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\search_api\SearchApiException
   * @throws \Drupal\search_api_autocomplete\SearchApiAutocompleteException
   */
  public static function supportsSearch(SearchInterface $search) {
    /** @var \Drupal\search_api_solr\SolrBackendInterface $backend */
    $backend = static::getBackend($search->getIndex());
    return ($backend && version_compare($backend->getSolrConnector()->getSolrMajorVersion(), '6', '>='));
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'search_api_solr/site_hash' => TRUE,
      'search_api/index' => '',
      'drupal/langcode' => 'any',
    ];
  }

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\search_api\SearchApiException
   * @throws \Drupal\search_api_autocomplete\SearchApiAutocompleteException
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $search = $this->getSearch();
    $server = $search->getIndex()->getServerInstance();

    $form['search_api_solr/site_hash'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('From this site only'),
      '#description' => $this->t('Limit the suggestion dictionary to entries created by this site in case of a multisite setup.'),
      '#default_value' => $this->getConfiguration()['search_api_solr/site_hash'],
    ];

    $index_options['any'] = $this->t('Any index');
    foreach ($server->getIndexes() as $index) {
      $index_options[$index->id()] = $this->t('Index @index', ['@index' => $index->label()]);
    }

    $form['search_api/index'] = [
      '#type' => 'radios',
      '#title' => $this->t('Index'),
      '#description' => $this->t('Limit the suggestion dictionary to entries to those created by a specific index.'),
      '#options' => $index_options,
      '#default_value' => $this->getConfiguration()['search_api/index'] ?: $search->getIndex()->id(),
    ];

    $langcode_options['any'] = $this->t('Any language');
    $langcode_options['multilingual'] = $this->t('Let the Solr server handle it dynamically.');
    foreach (\Drupal::languageManager()->getLanguages() as $language) {
      $langcode_options[$language->getId()] = $language->getName();
    }
    $langcode_options[LanguageInterface::LANGCODE_NOT_SPECIFIED] = $this->t('Undefined');

    $form['drupal/langcode'] = [
      '#type' => 'radios',
      '#title' => $this->t('Language'),
      '#description' => $this->t('Limit the suggestion dictionary to entries that belong to a specific language.'),
      '#options' => $langcode_options,
      '#default_value' => $this->getConfiguration()['drupal/langcode'],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $this->setConfiguration($values);
  }

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\search_api\SearchApiException
   * @throws \Drupal\search_api_autocomplete\SearchApiAutocompleteException
   */
  public function getAutocompleteSuggestions(QueryInterface $query, $incomplete_key, $user_input) {
    if (!($backend = static::getBackend($this->getSearch()->getIndex()))) {
      return [];
    }

    $config = $this->getConfiguration();
    $options['context_filter_tags'] = [];
    if ($config['search_api_solr/site_hash']) {
      $options['context_filter_tags'][] = 'search_api_solr/site_hash:' . Utility::getSiteHash();
    }
    if (!empty($config['search_api/index']) && 'any' !== $config['search_api/index']) {
      $options['context_filter_tags'][] = 'search_api/index:' . $config['search_api/index'];
    }
    if ('any' !== $config['drupal/langcode']) {
      $options['context_filter_tags'][] = 'drupal/langcode:' . $config['drupal/langcode'];
    }

    return $backend->getSuggesterSuggestions($query, $this->getSearch(), $incomplete_key, $user_input, $options);
  }

}
