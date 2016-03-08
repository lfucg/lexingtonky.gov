<?php
/**
 * @file
 * Contains \Drupal\chosen\Form\ChosenConfigForm.
 */

namespace Drupal\chosen\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\system\Form;
use Drupal\Core\Url;
use Drupal\Core\Link;

/**
 * Implements a ChosenConfig form.
 */
class ChosenConfigForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'chosen_config_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['chosen.settings'];
  }

  /**
   * Chosen configuration form.
   *
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $chosen_path = _chosen_lib_get_chosen_path();
    if (!$chosen_path) {
      $url = Url::fromUri(CHOSEN_WEBSITE_URL);
      $link = Link::fromTextAndUrl(t('Chosen JavaScript file'), $url)->toString();

      drupal_set_message(t('The library could not be detected. You need to download the !chosen and extract the entire contents of the archive into the %path directory on your server.',
        array('!chosen' => $link, '%path' => 'libraries')
      ), 'error');
      return $form;
    }

    // Chosen settings:
    $chosen_conf = $this->configFactory->get('chosen.settings');

    $form['chosen_minimum_single'] = array(
      '#type' => 'select',
      '#title' => t('Minimum number of options for single select'),
      '#options' => array_merge(array('0' => t('Always apply')), range(1, 25)),
      '#default_value' => $chosen_conf->get('chosen_minimum_single'),
      '#description' => t('The minimum number of options to apply Chosen for single select fields. Example : choosing 10 will only apply Chosen if the number of options is greater or equal to 10.'),
    );

    $form['chosen_minimum_multiple'] = array(
      '#type' => 'select',
      '#title' => t('Minimum number of options for multi select'),
      '#options' => array_merge(array('0' => t('Always apply')), range(1, 25)),
      '#default_value' => $chosen_conf->get('chosen_minimum_multiple'),
      '#description' => t('The minimum number of options to apply Chosen for multi select fields. Example : choosing 10 will only apply Chosen if the number of options is greater or equal to 10.'),
    );

    $form['chosen_disable_search_threshold'] = array(
      '#type' => 'select',
      '#title' => t('Minimum number to show Search on Single Select'),
      '#options' => array_merge(array('0' => t('Always apply')), range(1, 25)),
      '#default_value' => $chosen_conf->get('chosen_disable_search_threshold'),
      '#description' => t('The minimum number of options to apply Chosen search box. Example : choosing 10 will only apply Chosen search if the number of options is greater or equal to 10.'),
    );

    $form['chosen_minimum_width'] = array(
      '#type' => 'textfield',
      '#title' => t('Minimum width of widget'),
      '#field_suffix' => 'px',
      '#required' => TRUE,
      '#size' => 3,
      '#default_value' => $chosen_conf->get('chosen_minimum_width'),
      '#description' => t('The minimum width of the Chosen widget.'),
    );

    $form['chosen_jquery_selector'] = array(
      '#type' => 'textarea',
      '#title' => t('Apply Chosen to the following elements'),
      '#description' => t('A comma-separated list of jQuery selectors to apply Chosen to, such as <code>select#edit-operation, select#edit-type</code> or <code>.chosen-select</code>. Defaults to <code>select</code> to apply Chosen to all <code>&lt;select&gt;</code> elements.'),
      '#default_value' => $chosen_conf->get('chosen_jquery_selector'),
    );

    $form['options'] = array(
      '#type' => 'fieldset',
      '#title' => t('Chosen general options'),
    );

    $form['options']['chosen_search_contains'] = array(
      '#type' => 'checkbox',
      '#title' => t('Search also in the middle of words'),
      '#default_value' => $chosen_conf->get('chosen_search_contains'),
      '#description' => t('Per default chosen searches only at beginning of words. Enable this option will also find results in the middle of words.
      Example: Search for <em>land</em> will also find <code>Switzer<strong>land</strong></code>.'),
    );

    $form['options']['chosen_disable_search'] = array(
      '#type' => 'checkbox',
      '#title' => t('Disable search box'),
      '#default_value' => $chosen_conf->get('chosen_disable_search'),
      '#description' => t('Enable or disable the search box in the results list to filter out possible options.'),
    );

    $form['theme_options'] = array(
      '#type' => 'fieldset',
      '#title' => t('Chosen per theme options'),
    );

    $default_chosen_disabled_themes = $chosen_conf->get('chosen_disabled_themes');
    $default_chosen_disabled_themes = is_array($default_chosen_disabled_themes) ? $default_chosen_disabled_themes : array();
    $form['theme_options']['chosen_disabled_themes'] = array(
      '#type' => 'checkboxes',
      '#title' => t('Disable the default Chosen theme for the following themes'),
      '#options' => $this->chosen_enabled_themes_options(),
      '#default_value' => $default_chosen_disabled_themes,
      '#description' => t('Enable or disable the default Chosen CSS file. Select a theme if it contains custom styles for Chosen replacements.'),
    );

    $form['strings'] = array(
      '#type' => 'fieldset',
      '#title' => t('Chosen strings'),
    );

    $form['strings']['chosen_placeholder_text_multiple'] = array(
      '#type' => 'textfield',
      '#title' => t('Placeholder text of multiple selects'),
      '#required' => TRUE,
      '#default_value' => $chosen_conf->get('chosen_placeholder_text_multiple'),
    );

    $form['strings']['chosen_placeholder_text_single'] = array(
      '#type' => 'textfield',
      '#title' => t('Placeholder text of single selects'),
      '#required' => TRUE,
      '#default_value' => $chosen_conf->get('chosen_placeholder_text_single'),
    );

    $form['strings']['chosen_no_results_text'] = array(
      '#type' => 'textfield',
      '#title' => t('No results text'),
      '#required' => TRUE,
      '#default_value' => $chosen_conf->get('chosen_no_results_text'),
    );

    $form['submit'] = array(
      '#type' => 'submit',
      '#value' => t('submit'),
    );

    return $form;
  }

  /**
   * Chosen configuration form submit handler.
   *
   * Validates submission by checking for duplicate entries, invalid
   * characters, and that there is an abbreviation and phrase pair.
   *
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->configFactory->getEditable('chosen.settings');

    $config
      ->set('chosen_minimum_single', $form_state->getValue('chosen_minimum_single'))
      ->set('chosen_minimum_multiple', $form_state->getValue('chosen_minimum_multiple'))
      ->set('chosen_disable_search_threshold', $form_state->getValue('chosen_disable_search_threshold'))
      ->set('chosen_minimum_width', $form_state->getValue('chosen_minimum_width'))
      ->set('chosen_jquery_selector', $form_state->getValue('chosen_jquery_selector'))
      ->set('chosen_search_contains', $form_state->getValue('chosen_search_contains'))
      ->set('chosen_disable_search', $form_state->getValue('chosen_disable_search'))
      ->set('chosen_disabled_themes', $form_state->getValue('chosen_disabled_themes'))
      ->set('chosen_placeholder_text_multiple', $form_state->getValue('chosen_placeholder_text_multiple'))
      ->set('chosen_placeholder_text_single', $form_state->getValue('chosen_placeholder_text_single'))
      ->set('chosen_no_results_text', $form_state->getValue('chosen_no_results_text'));

    $config->save();

    parent::submitForm($form, $form_state);
  }

  /**
   * Helper function to get options for enabled themes
   */
  private function chosen_enabled_themes_options() {
    $options = array();

    // Get a list of available themes.
    $theme_handler = \Drupal::service('theme_handler');

    $themes = $theme_handler->listInfo();

    foreach ($themes as $theme_name => $theme) {
      // Only create options for enabled themes
      if ($theme->status) {
        if (!(isset($theme->info['hidden']) && $theme->info['hidden'])) {
          $options[$theme_name] = $theme->info['name'];
        }
      }
    }

    return $options;
  }

}
