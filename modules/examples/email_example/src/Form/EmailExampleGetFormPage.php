<?php

namespace Drupal\email_example\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Mail\MailManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Language\LanguageManagerInterface;

/**
 * File test form class.
 *
 * @ingroup email_example
 */
class EmailExampleGetFormPage extends FormBase {

  /**
   * The mail manager.
   *
   * @var \Drupal\Core\Mail\MailManagerInterface
   */
  protected $mailManager;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * Constructs a new EmailExampleGetFormPage.
   *
   * @param \Drupal\Core\Mail\MailManagerInterface $mail_manager
   *   The mail manager.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   */
  public function __construct(MailManagerInterface $mail_manager, LanguageManagerInterface $language_manager) {
    $this->mailManager = $mail_manager;
    $this->languageManager = $language_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.mail'),
      $container->get('language_manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'email_example';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['intro'] = array(
      '#markup' => t('Use this form to send a message to an e-mail address. No spamming!'),
    );
    $form['email'] = array(
      '#type' => 'textfield',
      '#title' => t('E-mail address'),
      '#required' => TRUE,
    );
    $form['message'] = array(
      '#type' => 'textarea',
      '#title' => t('Message'),
      '#required' => TRUE,
    );
    $form['submit'] = array(
      '#type' => 'submit',
      '#value' => t('Submit'),
    );
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    if (!valid_email_address($form_state->getValue('email'))) {
      $form_state->setErrorByName('email', t('That e-mail address is not valid.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $form_values = $form_state->getValues();

    // All system mails need to specify the module and template key (mirrored
    // from hook_mail()) that the message they want to send comes from.
    $module = 'email_example';
    $key = 'contact_message';

    // Specify 'to' and 'from' addresses.
    $to = $form_values['email'];
    $from = $this->config('system.site')->get('mail');

    // "params" loads in additional context for email content completion in
    // hook_mail(). In this case, we want to pass in the values the user entered
    // into the form, which include the message body in $form_values['message'].
    $params = $form_values;

    // The language of the e-mail. This will one of three values:
    // - $account->getPreferredLangcode(): Used for sending mail to a particular
    //   website user, so that the mail appears in their preferred language.
    // - \Drupal::currentUser()->getPreferredLangcode(): Used when sending a
    //   mail back to the user currently viewing the site. This will send it in
    //   the language they're currently using.
    // - \Drupal::languageManager()->getDefaultLanguage()->getId: Used when
    //   sending mail to a pre-existing, 'neutral' address, such as the system
    //   e-mail address, or when you're unsure of the language preferences of
    //   the intended recipient.
    //
    // Since in our case, we are sending a message to a random e-mail address
    // that is not necessarily tied to a user account, we will use the site's
    // default language.
    $language_code = $this->languageManager->getDefaultLanguage()->getId();

    // Whether or not to automatically send the mail when we call mail() on the
    // mail manager. This defaults to TRUE, and is normally what you want unless
    // you need to do additional processing before the mail manager sends the
    // message.
    $send_now = TRUE;
    // Send the mail, and check for success. Note that this does not guarantee
    // message delivery; only that there were no PHP-related issues encountered
    // while sending.
    $result = $this->mailManager->mail($module, $key, $to, $language_code, $params, $from, $send_now);
    if ($result['result'] == TRUE) {
      drupal_set_message(t('Your message has been sent.'));
    }
    else {
      drupal_set_message(t('There was a problem sending your message and it was not sent.'), 'error');
    }
  }

}
