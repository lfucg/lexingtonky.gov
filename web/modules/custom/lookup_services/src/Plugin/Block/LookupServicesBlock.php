<?php

namespace Drupal\lookup_services\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides the 'Lookup Services' Block for the homepage.
 *
 * @Block(
 *   id = "lookup_services_block",
 *   admin_label = @Translation("Lookup Services block"),
 *   category = @Translation("Custom"),
 * )
 */
class LookupServicesBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    // Return the form @ Form/LookupServicesBlockForm.php.
    return \Drupal::formBuilder()->getForm('Drupal\lookup_services\Form\LookupServicesBlockForm');
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);
    $config = $this->getConfiguration();

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->setConfigurationValue('lookup_services_block_settings', $form_state->getValue('lookup_services_block_settings'));
  }

}
