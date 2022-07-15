<?php

namespace Drupal\search_api_solr\Form;

use Drupal\Component\Render\FormattableMarkup;
use Drupal\Component\Utility\Html;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\search_api\Form\IndexAddFieldsForm;
use Drupal\search_api_solr\Plugin\search_api\datasource\SolrDocument;
use Drupal\search_api_solr\Plugin\search_api\datasource\SolrMultisiteDocument;

/**
 * Provides a form for adding fields to a search index.
 */
class IndexAddSolrDocumentFieldsForm extends IndexAddFieldsForm {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'search_api_index.add_solr_document_fields';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $index = $this->entity;

    // Do not allow the form to be cached. See
    // \Drupal\views_ui\ViewEditForm::form().
    $form_state->disableCache();

    $this->checkEntityEditable($form, $index);

    $args['%index'] = $index->label();
    $form['#title'] = $this->t('Add fields to index %index', $args);

    $this->formIdAttribute = Html::getUniqueId($this->getFormId());
    $form['#id'] = $this->formIdAttribute;

    $form['messages'] = [
      '#type' => 'status_messages',
    ];

    $datasources = [
      '' => NULL,
    ];
    $datasources += $this->entity->getDatasources();
    foreach ($datasources as $datasource_id => $datasource) {
      if ($datasource instanceof SolrDocument || $datasource instanceof SolrMultisiteDocument) {
        $item = $this->getDatasourceListItem($datasource);
        if ($item) {
          foreach ($index->getFields() as $field) {
            $id = $field->getFieldIdentifier();
            if (isset($item[$id])) {
              unset($item[$id]);
            }
          }

          $form['datasources']['datasource_' . $datasource_id] = $item;
        }
      }
    }

    $form['actions'] = $this->actionsElement($form, $form_state);

    // Log any unmapped types that were encountered.
    if ($this->unmappedFields) {
      $unmapped_fields = [];
      foreach ($this->unmappedFields as $type => $fields) {
        foreach ($fields as $field) {
          $unmapped_fields[] = new FormattableMarkup('@field (type "@type")', [
            '@field' => $field,
            '@type' => $type,
          ]);
        }
      }
      $form['unmapped_types'] = [
        '#type' => 'details',
        '#title' => $this->t('Skipped fields'),
        'fields' => [
          '#theme' => 'item_list',
          '#items' => $unmapped_fields,
          '#prefix' => $this->t('The following fields cannot be indexed since there is no type mapping for them:'),
          '#suffix' => $this->t("If you think one of these fields should be available for indexing, please report this in the module's <a href=':url'>issue queue</a>. (Make sure to first search for an existing issue for this field.) Please note that entity-valued fields generally can be indexed by either indexing their parent reference field, or their child entity ID field.", [':url' => Url::fromUri('https://www.drupal.org/project/issues/search_api')->toString()]),
        ],
      ];
    }

    return $form;
  }

}
