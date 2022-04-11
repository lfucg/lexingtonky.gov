<?php

namespace Drupal\search_api_solr_admin\Form;

use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\search_api\ServerInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\search_api_solr\SearchApiSolrException;
use Drupal\search_api_solr\Utility\Utility;
use Drupal\search_api_solr_admin\Utility\SolrAdminCommandHelper;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * The upload configset form.
 *
 * @package Drupal\search_api_solr_admin\Form
 */
class SolrUploadConfigsetForm extends SolrAdminFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'solr_upload_configset_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, ServerInterface $search_api_server = NULL) {
    $this->search_api_server = $search_api_server;

    $connector = Utility::getSolrCloudConnector($this->search_api_server);
    $configset = $connector->getConfigSetName();
    if (!$configset) {
      $this->messenger->addWarning($this->t('No existing configset name could be detected on the Solr server for this collection. That\'s fine if you just create a new collection. Otherwise you should check the logs.'));
    }

    $form['#title'] = $this->t('Upload Configset for %collection?', ['%collection' => $connector->getCollectionName()]);

    $form['accept'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Upload (and overwrite) configset %configset to Solr Server.', ['%configset' => $configset]),
      '#decrtiption' => $configset ? $this->t("The collection will be reloaded using the new configset") : $this->t('A new collection will be created from the configset.'),
      '#default_value' => FALSE,
    ];

    if (!$configset) {
      $form['num_shards'] = [
        '#type' => 'number',
        '#title' => $this->t('Number of shards'),
        '#description' => $this->t('The number of shards to be created for the collection.'),
        '#default_value' => 3,
      ];
    }
    else {
      $form['num_shards'] = [
        '#type' => 'value',
        '#default_value' => 3,
      ];
    }

    $form['actions'] = [
      'submit' => [
        '#type' => 'submit',
        '#value' => $this->t('Upload'),
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    if (!$form_state->getValue('accept')) {
      $form_state->setError($form['accept'], $this->t('You must accept the action that will be taken after the configset is uploaded.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    try {
      $this->commandHelper->uploadConfigset($this->search_api_server->id(), (int) $form_state->getValue('num_shards'), TRUE);
    }
    catch (\Exception $e) {
      $this->messenger->addError($e->getMessage());
      $this->logException($e);
    }

    $form_state->setRedirect('entity.search_api_server.canonical', ['search_api_server' => $this->search_api_server->id()]);
  }

}
