<?php

namespace Drupal\search_api_solr_admin\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\search_api\LoggerTrait;
use Drupal\search_api\ServerInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\search_api_solr\Utility\Utility;
use Drupal\search_api_solr_admin\Utility\SolrAdminCommandHelper;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * The core reload form.
 *
 * @package Drupal\search_api_solr_admin\Form
 */
class SolrReloadCoreForm extends SolrAdminFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'solr_reload_core_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, ServerInterface $search_api_server = NULL) {
    $this->search_api_server = $search_api_server;

    /** @var \Drupal\search_api_solr\Plugin\search_api\backend\SearchApiSolrBackend $backend */
    $backend = $this->search_api_server->getBackend();

    $core = $this->search_api_server->getBackendConfig()['connector_config']['core'];
    $form['#title'] = $this->t('Reload %type %core?', ['%type' => $backend->getSolrConnector()->isCloud() ? 'core' : 'collection', '%core' => $core]);

    $form['actions'] = [
      'submit' => [
        '#type' => 'submit',
        '#value' => $this->t('Reload'),
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    try {
      $this->commandHelper->reload($this->search_api_server->id());
      $this->messenger->addMessage($this->t('Successfully reloaded %type.', ['%type' => Utility::getSolrConnector($this->search_api_server)->isCloud() ? 'collection' : 'core']));
    }
    catch (\Exception $e) {
      $this->messenger->addError($e->getMessage());
      $this->logException($e);
    }

    $form_state->setRedirect('entity.search_api_server.canonical', ['search_api_server' => $this->search_api_server->id()]);
  }

}
