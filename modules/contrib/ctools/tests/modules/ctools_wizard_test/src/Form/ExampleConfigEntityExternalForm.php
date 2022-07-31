<?php

namespace Drupal\ctools_wizard_test\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Example form config entity.
 */
class ExampleConfigEntityExternalForm extends FormBase {

  /**
   * Tempstore factory.
   *
   * @var \Drupal\Core\TempStore\PrivateTempStoreFactory
   */
  protected $tempstore;

  /**
   * Constructs a new ExampleConfigEntityExternalForm.
   *
   * @param \Drupal\Core\TempStore\PrivateTempStoreFactory $tempstore
   *   Creates a private temporary storage for a collection.
   */
  public function __construct(PrivateTempStoreFactory $tempstore) {
    $this->tempstore = $tempstore;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('tempstore.private'));
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'ctools_wizard_test_example_config_entity_external_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $machine_name = '') {
    $cached_values = $this->tempstore->get('ctools_wizard_test.config_entity')->get($machine_name);
    /** @var \Drupal\ctools_wizard_test\Entity\ExampleConfigEntity $page */
    $config_entity = $cached_values['ctools_wizard_test_config_entity'];

    $form['blah'] = [
      '#markup' => 'Value from one: ' . $config_entity->getOne(),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Don't do anything.
  }

}
