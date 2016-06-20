<?php

/**
 * @file
 * Contains \Drupal\autologout\Form\AutologoutBlockForm.
 */

namespace Drupal\autologout\Form;

use Drupal\autologout\AutologoutManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a settings for autologout module.
 */
class AutologoutBlockForm extends FormBase {

  /**
   * The autologout manager service.
   *
   * @var \Drupal\autologout\AutologoutManagerInterface
   */
  protected $autoLogoutManager;

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'autologout_block_settings';
  }

  /**
   * Constructs an AutologoutBlockForm object.
   *
   * @param \Drupal\autologout\AutologoutManagerInterface $autologout
   *   The autologout manager service.
   */
  public function __construct(AutologoutManagerInterface $autologout) {
    $this->autoLogoutManager = $autologout;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('autologout.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['reset'] = array(
      '#type' => 'button',
      '#value' => t('Reset Timeout'),
      '#weight' => 1,
      '#limit_validation_errors' => FALSE,
      '#executes_submit_callback' => FALSE,
      '#ajax' => array(
        'callback' => 'autologout_ahah_set_last',
      ),
    );

    $form['timer'] = array(
      '#markup' => $this->autoLogoutManager->createTimer(),
    );

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Submits on block form.
  }

}
