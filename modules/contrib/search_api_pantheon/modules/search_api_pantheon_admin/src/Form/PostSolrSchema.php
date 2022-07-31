<?php

namespace Drupal\search_api_pantheon_admin\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\search_api\ServerInterface;
use Drupal\search_api_pantheon\Services\SchemaPoster;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Finder\Finder;

/**
 * The Solr admin form.
 *
 * @package Drupal\search_api_pantheon\Form
 */
class PostSolrSchema extends FormBase {

  /**
   * The PantheonGuzzle service.
   *
   * @var \Drupal\search_api_pantheon\Services\SchemaPoster
   */
  protected SchemaPoster $schemaPoster;

  /**
   * Search api server.
   *
   * @var \Drupal\search_api\ServerInterface
   */
  protected ServerInterface $server;

  /**
   * Constructs a new EntityController.
   */
  public function __construct(SchemaPoster $schemaPoster) {
    $this->schemaPoster = $schemaPoster;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
          $container->get('search_api_pantheon.schema_poster'),
      );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'search_api_solr_admin_post_schema';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, ServerInterface $search_api_server = NULL) {
    $this->server = $search_api_server;

    $form['path'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Path to config files to post'),
      '#description' => $this->t('Path to the config files to post. This should be a directory containing the configuration files to post. Leave empty to use search_api_solr defaults.'),
      '#default_value' => '',
      '#required' => FALSE,
      '#attributes' => [
        'placeholder' => $this->t('Leave empty to use search_api_solr defaults.'),
      ],
    ];

    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Post Schema'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $path = $form_state->getValue('path');
    if ($path) {
      if (!is_dir($path)) {
        $form_state->setErrorByName('path', $this->t('The path %path is not a directory.', ['%path' => $path]));
        return;
      }
      $finder = new Finder();
      // Only work with direct children.
      $finder->depth('== 0');
      $finder->files()->in($path);
      if (!$finder->hasResults()) {
        $form_state->setErrorByName('path', $this->t('The path %path does not contain any files.', ['%path' => $path]));
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $path = $form_state->getValue('path');
    $files = [];
    if ($path) {
      $finder = new Finder();
      // Only work with direct children.
      $finder->depth('== 0');
      $finder->files()->in($path);
      foreach ($finder as $file) {
        $files[$file->getfilename()] = $file->getContents();
      }
    }
    $message = $this->schemaPoster->postSchema($this->server->id(), $files);
    $method = $this->getMessageFunction($message[0]);
    $this->messenger()->{$method}($message[1]);
  }

  /**
   * Get the right function to call based on the message type.
   */
  protected function getMessageFunction(string $type) {
    $functions = [
      'info' => 'addStatus',
      'error' => 'addError',
    ];
    if (isset($functions[$type])) {
      return $functions[$type];
    }

    $this->messenger()->addWarning(t('Unknown message type: @type', ['@type' => $message[0]]));
    return 'addStatus';
  }

}
