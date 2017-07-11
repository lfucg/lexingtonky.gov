<?php

namespace Drupal\dropzonejs\Element;

use Drupal\Component\Utility\Bytes;
use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element\FormElement;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Url;

/**
 * Provides a DropzoneJS atop of the file element.
 *
 * Configuration options are:
 * - #title
 *   The main field title.
 * - #description
 *   Description under the field.
 * - #dropzone_description
 *   Will be visible inside the upload area.
 * - #max_filesize
 *   Used by dropzonejs and expressed in number + unit (i.e. 1.1M) This will be
 *   converted to a form that DropzoneJs understands. See:
 *   http://www.dropzonejs.com/#config-maxFilesize
 * - #extensions
 *   A string of valid extensions separated by a space.
 * - #max_files
 *   Number of files that can be uploaded.
 *   If < 1, there is no limit.
 *
 * When submitted the element returns an array of temporary file locations. It's
 * the duty of the environment that implements this element to handle the
 * uploaded files.
 *
 * @FormElement("dropzonejs")
 */
class DropzoneJs extends FormElement {

  /**
   * A defualut set of valid extensions.
   */
  const DEFAULT_VALID_EXTENSIONS = 'jpg jpeg gif png txt doc xls pdf ppt pps odt ods odp';

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    $class = get_class($this);
    return [
      '#input' => TRUE,
      '#process' => [[$class, 'processDropzoneJs']],
      '#pre_render' => [[$class, 'preRenderDropzoneJs']],
      '#theme' => 'dropzonejs',
      '#theme_wrappers' => ['form_element'],
      '#tree' => TRUE,
      '#attached' => [
        'library' => ['dropzonejs/integration'],
      ],
    ];
  }

  /**
   * Processes a dropzone upload element.
   */
  public static function processDropzoneJs(&$element, FormStateInterface $form_state, &$complete_form) {
    $element['uploaded_files'] = [
      '#type' => 'hidden',
      // @todo Handle defaults.
      '#default_value' => '',
      // If we send a url with a token through drupalSettings the placeholder
      // doesn't get replaced, because the actual scripts markup is not there
      // yet. So we pass this information through a data attribute.
      '#attributes' => ['data-upload-path' => Url::fromRoute('dropzonejs.upload')->toString()],
    ];

    if (empty($element['#max_filesize'])) {
      $element['#max_filesize'] = file_upload_max_size();
    }

    // Set #max_files to NULL (explicitly unlimited) if #max_files is not
    // specified.
    if (empty($element['#max_files'])) {
      $element['#max_files'] = NULL;
    }

    if (!\Drupal::currentUser()->hasPermission('dropzone upload files')) {
      $element['#access'] = FALSE;
      drupal_set_message(new TranslatableMarkup("You don't have sufficent permissions to use the DropzoneJS uploader. Contact your system administrator"), 'warning');
    }

    return $element;
  }

  /**
   * Prepares a #type 'dropzone' render element for dropzonejs.html.twig.
   *
   * @param array $element
   *   An associative array containing the properties of the element.
   *   Properties used: #title, #description, #required, #attributes,
   *   #dropzone_description, #max_filesize.
   *
   * @return array
   *   The $element with prepared variables ready for input.html.twig.
   */
  public static function preRenderDropzoneJs(array $element) {
    // Convert the human size input to bytes, convert it to MB and round it.
    $max_size = round(Bytes::toInt($element['#max_filesize']) / pow(Bytes::KILOBYTE, 2), 2);

    $element['#attached']['drupalSettings']['dropzonejs'] = [
      'instances' => [
        // Configuration keys are matched with DropzoneJS configuration
        // options.
        $element['#id'] => [
          'maxFilesize' => $max_size,
          'dictDefaultMessage' => Html::escape($element['#dropzone_description']),
          'acceptedFiles' => '.' . str_replace(' ', ',.', self::getValidExtensions($element)),
          'maxFiles' => $element['#max_files'],
        ],
      ],
    ];

    static::setAttributes($element, ['dropzone-enable']);
    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public static function valueCallback(&$element, $input, FormStateInterface $form_state) {
    $return['uploaded_files'] = [];

    if ($input !== FALSE) {
      $user_input = NestedArray::getValue($form_state->getUserInput(), $element['#parents'] + ['uploaded_files']);

      if (!empty($user_input['uploaded_files'])) {
        $file_names = array_filter(explode(';', $user_input['uploaded_files']));
        $tmp_upload_scheme = \Drupal::configFactory()->get('dropzonejs.settings')->get('tmp_upload_scheme');

        foreach ($file_names as $name) {
          // The upload handler appended the txt extension to the file for
          // security reasons. We will remove it in this callback.
          $old_filepath = $tmp_upload_scheme . '://' . $name;

          // The upload handler appended the txt extension to the file for
          // security reasons. Because here we know the acceptable extensions
          // we can remove that extension and sanitize the filename.
          $name = self::fixTmpFilename($name);
          $name = file_munge_filename($name, self::getValidExtensions($element));

          // Potentially we moved the file already, so let's check first whether
          // we still have to move.
          if (file_exists($old_filepath)) {
            // Finaly rename the file and add it to results.
            $new_filepath = $tmp_upload_scheme . '://' . $name;
            $move_result = file_unmanaged_move($old_filepath, $new_filepath);

            if ($move_result) {
              $return['uploaded_files'][] = [
                'path' => $move_result,
                'filename' => $name,
              ];
            }
            else {
              drupal_set_message(self::t('There was a problem while processing the file named @name', ['@name' => $name]), 'error');
            }
          }
        }
      }
      $form_state->setValueForElement($element, $return);
    }
    return $return;
  }

  /**
   * Gets valid file extensions for this element.
   *
   * @param array $element
   *   The element array.
   *
   * @return string
   *   A space separated list of extensions.
   */
  public static function getValidExtensions(array $element) {
    return isset($element['#extensions']) ? $element['#extensions'] : self::DEFAULT_VALID_EXTENSIONS;
  }

  /**
   * Fix temporary filename.
   *
   * The upload handler appended the txt extension to the file for
   * security reasons.
   *
   * @param string $filename
   *   The filename we need to fix.
   *
   * @return string
   *   The fixed filename.
   */
  public static function fixTmpFilename($filename) {
    $parts = explode('.', $filename);
    array_pop($parts);
    return implode('.', $parts);
  }

}
