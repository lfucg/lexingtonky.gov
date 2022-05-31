<?php

namespace Drupal\iframe\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\UrlHelper;

/**
 * Plugin implementation base functions.
 */
class IframeWidgetBase extends WidgetBase {

  /**
   * Allowed editable attributes of iframe field on node-edit.
   *
   * @var array
   */
  public $allowedAttributes = [
    'title' => 1,
    'url' => 1,
    'headerlevel' => 1,
    'width' => 1,
    'height' => 1,
    'tokensupport' => 1,
  ];

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
        'width' => '',
        'height' => '',
        'headerlevel' => '3',
        'class' => '',
        'expose_class' => 0,
        'frameborder' => '0',
        'scrolling' => 'auto',
        'transparency' => '0',
        'tokensupport' => '0',
        'allowfullscreen' => '0',
    ] + parent::defaultSettings();
  }

  /**
   * Translate the description for iframe width/height only once.
   */
  protected static function getSizedescription() {
    return t('The iframe\'s width and height can be set in pixels as a number only ("500" for 500 pixels) or in a percentage value followed by the percent symbol (%) ("50%" for 50 percent).');
  }

  /**
   * It is {@inheritdoc}.
   *
   * Used : at "Manage form display" after work-symbol.
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    /* Settings form after "manage form display" page, valid for one content type */
    $field_settings = $this->getFieldSettings();
    $widget_settings = $this->getSettings();
    // \iframe_debug(0, 'manage settingsForm widget_settings', $widget_settings);
    // \iframe_debug(0, 'manage settingsForm field_settings', $field_settings);

    $settings = [];
    foreach($widget_settings as $wkey => $wvalue) {
      if (empty($wvalue) && isset($field_settings[$wkey])) {
        $settings[$wkey] = $field_settings[$wkey];
      }
      else {
        $settings[$wkey] = $wvalue;
      }
    }
    $settings = $settings + $field_settings + self::defaultSettings();
    // \iframe_debug(0, 'manage settingsForm settings', $settings);
    /* NOW all values have their default values at minimum */

    // widget width/heigth wins, only if empty, then field-width/height are taken
    $element['width'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Iframe Width'),
      // ''
      '#default_value' => $settings['width'],
      '#description' => self::getSizedescription(),
      '#maxlength' => 4,
      '#size' => 4,
    ];
    $element['height'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Iframe Height'),
      // ''
      '#default_value' => $settings['height'],
      '#description' => self::getSizedescription(),
      '#maxlength' => 4,
      '#size' => 4,
    ];
    $element['headerlevel'] = [
      '#type' => 'select',
      '#title' => $this->t('Header Level'),
      '#options' => [
        '1' => $this->t('h1'),
        '2' => $this->t('h2'),
        '3' => $this->t('h3'),
        '4' => $this->t('h4'),
      ],
      // 0
      '#default_value' => $settings['headerlevel'],
      '#description' => $this->t('Header level for accessibility, defaults to "h3".'),
    ];
    $element['class'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Additional CSS Class'),
      // ''
      '#default_value' => $settings['class'],
      '#description' => $this->t('When output, this iframe will have this class attribute. Multiple classes should be separated by spaces.'),
    ];
    $element['expose_class'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Expose Additional CSS Class'),
      // 0
      '#default_value' => $settings['expose_class'],
      '#description' => $this->t('Allow author to specify an additional class attribute for this iframe.'),
    ];
    $element['frameborder'] = [
      '#type' => 'select',
      '#title' => $this->t('Frameborder'),
      '#options' => ['0' => $this->t('No frameborder'), '1' => $this->t('Show frameborder')],
      // 0
      '#default_value' => $settings['frameborder'],
      '#description' => $this->t('Frameborder is the border around the iframe. Most people want it removed, so the default value for frameborder is zero (0), or no border.'),
    ];
    $element['scrolling'] = [
      '#type' => 'select',
      '#title' => $this->t('Scrolling'),
      '#options' => [
        'auto' => $this->t('Automatic'),
        'no' => $this->t('Disabled'),
        'yes' => $this->t('Enabled'),
      ],
      // 'auto'
      '#default_value' => $settings['scrolling'],
      '#description' => $this->t('Scrollbars help the user to reach all iframe content despite the real height of the iframe content. Please disable it only if you know what you are doing.'),
    ];
    $element['transparency'] = [
      '#type' => 'select',
      '#title' => $this->t('Transparency'),
      '#options' => [
        '0' => $this->t('No transparency'),
        '1' => $this->t('Allow transparency'),
      ],
      // 0
      '#default_value' => $settings['transparency'],
      '#description' => $this->t('Allow transparency per CSS in the outer iframe tag. You have to set background-color:transparent in your iframe body tag too!'),
    ];
    $element['allowfullscreen'] = [
      '#type' => 'select',
      '#title' => $this->t('Allow fullscreen'),
      '#options' => [
        '0' => $this->t('false'),
        '1' => $this->t('true'),
      ],
      // 0
      '#default_value' => $settings['allowfullscreen'],
      '#description' => $this->t('Allow fullscreen for iframe. The iframe can activate fullscreen mode by calling the requestFullscreen() method.'),
    ];

    if (!\Drupal::moduleHandler()->moduleExists('token')) {
      $element['tokensupport']['#description'] .= ' ' . $this->t('Attention: Token module is not currently enabled!');
    }
    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $widget_settings = $this->getSettings();
    $field_settings = $this->getFieldSettings();
    // \iframe_debug(0, 'settingsSummary widget_settings', $widget_settings);
    // \iframe_debug(0, 'settingsSummary field_settings', $field_settings);

    $settings = [];
    foreach($widget_settings as $wkey => $wvalue) {
      if (empty($wvalue) && isset($field_settings[$wkey])) {
        $settings[$wkey] = $field_settings[$wkey];
      }
      else {
        $settings[$wkey] = $wvalue;
      }
    }
    $settings = $settings + $field_settings + self::defaultSettings();

    /* summary on the "manage display" page, valid for one content type */
    $summary = [];
    $summary[] = $this->t('Iframe default header level: h@level', ['@level' => $settings['headerlevel']]);
    $summary[] = $this->t('Iframe default width: @width', ['@width' => $settings['width']]);
    $summary[] = $this->t('Iframe default height: @height', ['@height' => $settings['height']]);
    $summary[] = $this->t('Iframe default frameborder: @frameborder', ['@frameborder' => $settings['frameborder']]);
    $summary[] = $this->t('Iframe default scrolling: @scrolling', ['@scrolling' => $settings['scrolling']]);

    return $summary;
  }

  /**
   * It is {@inheritdoc}.
   *
   * Used: (1) at admin edit fields.
   *
   * Used: (2) at add-story for creation content.
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    // 1) Shows the "default fields" in the edit-type-field page
    // -- (on_admin_page = true).
    // 2) Edit-fields on the article-edit-page (on_admin_page = false).
    // Global settings.
    // getSettings from manage form display after work-symbol (admin/structure/types/manage/test/form-display and wheel behind iframe-field)
    $widget_settings = $this->getSettings();
    // getFieldSettings from field edit page on admin/structure/types/manage/test/fields/node.test.field_iframe
    $field_settings = $this->getFieldSettings();
    // \iframe_debug(0, 'formElement widget_settings', $widget_settings);
    // \iframe_debug(0, 'formElement field_settings', $field_settings);
    // \iframe_debug(0, 'formElement defaultSettings', self::defaultSettings());

    /** @var \Drupal\iframe\Plugin\Field\FieldType\IframeItem $item */
    $item =& $items[$delta];
    $field_definition = $item->getFieldDefinition();
    /* on_admin_page TRUE only if on field edit page, not on widget-edit */
    $on_admin_page = isset($element['#field_parents'][0]) && ('default_value_input' == $element['#field_parents'][0]);
    $is_new = $item->getEntity()->isNew();
    // \iframe_debug(0, 'formElement onAdminPage', $on_admin_page ? "TRUE" : "false");
    // \iframe_debug(0, 'formElement isNew', $is_new ? "TRUE" : "false");
    $values = $item->toArray();

    $settings = [];
    /* take widget_settings only if NOT on_admin_page (so not on field-edit-page, where we edit the field_settings) */
    if (!$on_admin_page) {
      foreach($widget_settings as $wkey => $wvalue) {
        if (empty($wvalue) && isset($field_settings[$wkey])) {
          $settings[$wkey] = $field_settings[$wkey];
        }
        else {
          $settings[$wkey] = $wvalue;
        }
      }
    }
    $settings = $settings + $field_settings + self::defaultSettings();

    if ($is_new || $on_admin_page) {
      foreach ($values as $vkey => $vval) {
        if ($vval !== NULL && $vval !== '') {
          $settings[$vkey] = $vval;
        }
      }
    }
    else {
      if (isset($settings['expose_class']) && $settings['expose_class']) {
        $this->allowedAttributes['class'] = 1;
      }
      foreach ($this->allowedAttributes as $attribute => $attrAllowed) {
        if ($attrAllowed) {
          $settings[$attribute] = $values[$attribute];
        }
      }
    }
    // \iframe_debug(0, 'add-story formElement final settings', $settings);
    foreach ($settings as $attribute => $attrValue) {
      $item->setValue($attribute, $attrValue);
    }

    $element += [
      '#type' => 'fieldset',
      '#collapsible' => TRUE,
      '#open' => TRUE,
    ];
    if (!$on_admin_page) {
      $element['#title'] = $field_definition->getLabel();
    }

    /* if field is required, then url/width/height should be shown as required too! */
    $required = [];
    if (!empty($element['#required'])) {
      $required['#required'] = TRUE;
    }

    $element['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Iframe Title'),
      '#placeholder' => '',
      '#default_value' => $settings['title'],
      '#size' => 80,
      '#maxlength' => 1024,
      '#weight' => 2,
      // '#element_validate' => array('text'),
    ] + $required;

    $element['url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Iframe URL'),
      '#placeholder' => 'https://',
      '#default_value' => isset($settings['url']) ? $settings['url'] : '',
      '#size' => 80,
      '#maxlength' => 1024,
      '#weight' => 1,
      '#element_validate' => [[$this, 'validateUrl']],
    ] + $required;

    $element['width'] = [
      '#title' => $this->t('Iframe Width'),
      '#type' => 'textfield',
      '#default_value' => isset($settings['width']) ? $settings['width'] : '',
      '#description' => self::getSizedescription(),
      '#maxlength' => 4,
      '#size' => 4,
      '#weight' => 3,
      '#element_validate' => [[$this, 'validateWidth']],
    ] + $required;
    $element['height'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Iframe Height'),
      '#default_value' => isset($settings['height']) ? $settings['height'] : '',
      '#description' => self::getSizedescription(),
      '#maxlength' => 4,
      '#size' => 4,
      '#weight' => 4,
      '#element_validate' => [[$this, 'validateHeight']],
    ] + $required;
    if (isset($settings['expose_class']) && $settings['expose_class']) {
      $element['class'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Additional CSS Class'),
        // ''
        '#default_value' => $settings['class'],
        '#description' => $this->t('When output, this iframe will have this class attribute. Multiple classes should be separated by spaces.'),
        '#weight' => 5,
      ];
    }
    return $element;
  }

  /**
   * Validate width(if minimum url is defined)
   *
   * @see \Drupal\Core\Form\FormValidator
   */
  public function validateWidth(&$form, FormStateInterface &$form_state) {
    // get settings for this field
    $settings = $this->getFieldSettings();
    $me = $this->getField($form, $form_state);

    // \iframe_debug(0, 'validateWidth', $me);
    if (!empty($me['url']) && isset($me['width'])) {
      if (empty($me['width']) || !preg_match('#^(\d+\%?|auto)$#', $me['width'])) {
        $form_state->setError($form, self::getSizedescription());
      }
    }
  }

  /**
   * Validate height (if minimum url is defined)
   *
   * @see \Drupal\Core\Form\FormValidator
   */
  public function validateHeight(&$form, FormStateInterface &$form_state) {
    // get settings for this field
    $settings = $this->getFieldSettings();
    $me = $this->getField($form, $form_state);

    // \iframe_debug(0, 'validateHeight', $me);
    if (!empty($me['url']) && isset($me['height'])) {
      if (empty($me['height']) || !preg_match('#^(\d+\%?|auto)$#', $me['height'])) {
        $form_state->setError($form, self::getSizedescription());
      }
    }
  }

  /**
   * Validate url.
   *
   * @see \Drupal\Core\Form\FormValidator
   */
  public function validateUrl(&$form, FormStateInterface &$form_state) {
    // get settings for this field
    $settings = $this->getFieldSettings();
    $me = $this->getField($form, $form_state);
    if (isset($settings['tokensupport'])) {
      $tokensupport = $settings['tokensupport'];
    }
    else {
      $tokensupport = 0;
    }
    if ($tokensupport == 2) {
      $tokencontext = ['user' => \Drupal::currentUser()];
      $me['url'] = \Drupal::token()->replace($me['url'], $tokencontext);
    }

    $testabsolute = true;
    // \iframe_debug(0, 'validateUrl', $me);
    if (!empty($me['url'])) {
      if (preg_match('#^/($|[^/])#', $me['url'])) {
        $testabsolute = false;
      }
      if (!UrlHelper::isValid($me['url'], $testabsolute)) {
        $form_state->setError($form, t('Invalid syntax for "Iframe URL".'));
      }
      elseif (strpos($me['url'], '//') === 0) {
        $form_state->setError($form, t('Drupal does not accept scheme-less URLs. Please add "https:" to your URL, this works on http-parent-pages too.'));
      }
    }
  }

  /**
   * Return the field values.
   *
   * @param array $form
   *   The form structure where widgets are being attached to. This might be a
   *   full form structure, or a sub-element of a larger form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   */
  private function getField(&$form, FormStateInterface &$form_state) {
    $parents = $form['#parents'];
    $node = $form_state->getUserInput();

    // Remove the field property from the list of parents.
    array_pop($parents);

    // Starting from the node drill down to the field.
    $field = $node;
    for($i = 0; $i < count($parents); $i++) {
      $field = $field[$parents[$i]];
    }

    return $field;
  }

  /**
   * {@inheritdoc}
   */
  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {
    // Global values.
    $field_settings = $this->getFieldSettings();
    $settings = $this->getSettings() + $field_settings;

    if (isset($settings['expose_class']) && $settings['expose_class']) {
      $this->allowedAttributes['class'] = 1;
    }

    // \iframe_debug(0, __METHOD__ . ' settings', $settings);
    // \iframe_debug(0, __METHOD__ . ' old-values', $values);
    foreach ($values as $delta => $value) {
      /*
       * Validate that all keys are available,
       * in the user-has-only-some-values case too.
       */
      $testvalue = $value + $settings;
      $newvalue = [];

      foreach ($testvalue as $key => $val) {
        if (
          isset($this->allowedAttributes[$key])
          && $this->allowedAttributes[$key]
        ) {
          $newvalue[$key] = $val;
        }
        elseif (isset($settings[$key])) {
          $newvalue[$key] = $settings[$key];
        }
        else {
          $newvalue[$key] = $val;
        }
      }
      if (!empty($settings['class']) && !strstr($newvalue['class'], $settings['class'])) {
        $newvalue['class'] = trim(implode(" ", [$settings['class'], $newvalue['class']]));
      }
      $new_values[$delta] = $newvalue;
    }
    // \iframe_debug(0, __METHOD__ . ' new-values', $new_values);
    return $new_values;
  }

}
