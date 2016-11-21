<?php

namespace Drupal\dbtng_example\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\dbtng_example\DbtngExampleStorage;

/**
 * Sample UI to update a record.
 */
class DbtngExampleUpdateForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'dbtng_update_form';
  }

  /**
   * Sample UI to update a record.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Wrap the form in a div.
    $form = array(
      '#prefix' => '<div id="updateform">',
      '#suffix' => '</div>',
    );
    // Add some explanatory text to the form.
    $form['message'] = array(
      '#markup' => $this->t('Demonstrates a database update operation.'),
    );
    // Query for items to display.
    $entries = DbtngExampleStorage::load();
    // Tell the user if there is nothing to display.
    if (empty($entries)) {
      $form['no_values'] = array(
        '#value' => t('No entries exist in the table dbtng_example table.'),
      );
      return $form;
    }

    $keyed_entries = array();
    foreach ($entries as $entry) {
      $options[$entry->pid] = t('@pid: @name @surname (@age)', array(
        '@pid' => $entry->pid,
        '@name' => $entry->name,
        '@surname' => $entry->surname,
        '@age' => $entry->age,
      ));
      $keyed_entries[$entry->pid] = $entry;
    }

    // Grab the pid.
    $pid = $form_state->getValue('pid');
    // Use the pid to set the default entry for updating.
    $default_entry = !empty($pid) ? $keyed_entries[$pid] : $entries[0];

    // Save the entries into the $form_state. We do this so the AJAX callback
    // doesn't need to repeat the query.
    $form_state->setValue('entries', $keyed_entries);

    $form['pid'] = array(
      '#type' => 'select',
      '#options' => $options,
      '#title' => t('Choose entry to update'),
      '#default_value' => $default_entry->pid,
      '#ajax' => array(
        'wrapper' => 'updateform',
        'callback' => array($this, 'updateCallback'),
      ),
    );

    $form['name'] = array(
      '#type' => 'textfield',
      '#title' => t('Updated first name'),
      '#size' => 15,
      '#default_value' => $default_entry->name,
    );

    $form['surname'] = array(
      '#type' => 'textfield',
      '#title' => t('Updated last name'),
      '#size' => 15,
      '#default_value' => $default_entry->surname,
    );
    $form['age'] = array(
      '#type' => 'textfield',
      '#title' => t('Updated age'),
      '#size' => 4,
      '#default_value' => $default_entry->age,
      '#description' => t('Values greater than 127 will cause an exception'),
    );

    $form['submit'] = array(
      '#type' => 'submit',
      '#value' => t('Update'),
    );
    return $form;
  }

  /**
   * AJAX callback handler for the pid select.
   *
   * When the pid changes, populates the defaults from the database in the form.
   */
  public function updateCallback(array $form, FormStateInterface $form_state) {
    // Gather the DB results from $form_state.
    $entries = $form_state->getValue('entries');
    // Use the specific entry for this $form_state.
    $entry = $entries[$form_state->getValue('pid')];
    // Setting the #value of items is the only way I was able to figure out
    // to get replaced defaults on these items. #default_value will not do it
    // and shouldn't.
    foreach (array('name', 'surname', 'age') as $item) {
      $form[$item]['#value'] = $entry->$item;
    }
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // Confirm that age is numeric.
    if (!intval($form_state->getValue('age'))) {
      $form_state->setErrorByName('age', t('Age needs to be a number'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Gather the current user so the new record has ownership.
    $account = $this->currentUser();
    // Save the submitted entry.
    $entry = array(
      'pid' => $form_state->getValue('pid'),
      'name' => $form_state->getValue('name'),
      'surname' => $form_state->getValue('surname'),
      'age' => $form_state->getValue('age'),
      'uid' => $account->id(),
    );
    $count = DbtngExampleStorage::update($entry);
    drupal_set_message(t('Updated entry @entry (@count row updated)', array(
      '@count' => $count,
      '@entry' => print_r($entry, TRUE),
    )));
  }

}
