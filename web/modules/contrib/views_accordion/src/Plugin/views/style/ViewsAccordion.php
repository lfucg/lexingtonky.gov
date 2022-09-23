<?php

namespace Drupal\views_accordion\Plugin\views\style;

use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\style\StylePluginBase;

/**
 * Style plugin to render each item in an ordered or unordered list.
 *
 * @ingroup views_style_plugins
 *
 * @ViewsStyle(
 *   id = "views_accordion",
 *   title = @Translation("jQuery UI accordion"),
 *   help = @Translation("Display a JQuery accordion with the results. The first field will be used as the header and trigger."),
 *   theme = "views_accordion_view",
 *   display_types = {"normal"}
 * )
 */
class ViewsAccordion extends StylePluginBase {
  /**
   * {@inheritdoc}
   */
  protected $usesRowPlugin = TRUE;

  /**
   * {@inheritdoc}
   */
  protected $usesRowClass = TRUE;

  /**
   * Set default options.
   */
  protected function defineOptions() {
    $options = parent::defineOptions();
    $options['use-grouping-header'] = ['default' => 0];
    $options['collapsible'] = ['default' => 0];
    $options['row-start-open'] = ['default' => 0];
    $options['animated'] = ['default' => 'slide'];
    $options['animation_time'] = ['default' => 300];
    $options['heightStyle'] = ['default' => 'auto'];
    $options['event'] = ['default' => 'click'];
    $options['disableifone'] = ['default' => 0];
    $options['use_header_icons'] = ['default' => TRUE];
    $options['icon_header'] = ['default' => 'ui-icon-triangle-1-e'];
    $options['icon_active_header'] = ['default' => 'ui-icon-triangle-1-s'];

    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);

    // Find out how many items the display is currently configured to show
    // (row-start-open).
    $maxitems = $this->displayHandler->getOption('items_per_page');
    // If items_per_page is set to unlimitted (0), 10 rows will be what the user
    // gets to choose from.
    $maxitems = ($maxitems == 0) ? 10 : $maxitems;

    // Setup our array of options for choosing which row should start opened
    // (row-start-open).
    $rsopen_options = [];
    for ($i = 1; $i <= $maxitems; $i++) {
      $rsopen_options[] = $this->t('Row @number', ['@number' => $i]);
    }
    $rsopen_options['none'] = $this->t('None');
    $rsopen_options['random'] = $this->t('Random');

    /*
     * See /core/core.libraries.yml and http://api.jqueryui.com/1.10/easings/
     * We've used all the easing effects form ui effects core.
     */
    $animated_options = [
      'none' => $this->t('None'),
      'linear' => $this->t('Linear'),
      'swing' => $this->t('Swing'),
      'easeInQuart' => $this->t('easeInQuart'),
      'easeOutQuart' => $this->t('easeOutQuart'),
      'easeInOutQuart' => $this->t('easeInOutQuart'),
      'easeInExpo' => $this->t('easeInExpo'),
      'easeOutExpo' => $this->t('easeOutExpo'),
      'easeInOutExpo' => $this->t('easeInOutExpo'),
      'easeInBack' => $this->t('easeInBack'),
      'easeOutBack' => $this->t('easeOutBack'),
      'easeInOutBack' => $this->t('easeInOutBack'),
      'easeInQuad' => $this->t('easeInQuad'),
      'easeOutQuad' => $this->t('easeOutQuad'),
      'easeInOutQuad' => $this->t('easeInOutQuad'),
      'easeInQuint' => $this->t('easeInQuint'),
      'easeOutQuint' => $this->t('easeOutQuint'),
      'easeInOutQuint' => $this->t('easeInOutQuint'),
      'easeInCirc' => $this->t('easeInCirc'),
      'easeOutCirc' => $this->t('easeOutCirc'),
      'easeInOutCirc' => $this->t('easeInOutCirc'),
      'easeInBounce' => $this->t('easeInBounce'),
      'easeOutBounce' => $this->t('easeOutBounce'),
      'easeInOutBounce' => $this->t('easeInOutBounce'),
      'easeInCubic' => $this->t('easeInCubic'),
      'easeOutCubic' => $this->t('easeOutCubic'),
      'easeInOutCubic' => $this->t('easeInOutCubic'),
      'easeInSine' => $this->t('easeInSine'),
      'easeOutSine' => $this->t('easeOutSine'),
      'easeInOutSine' => $this->t('easeInOutSine'),
      'easeInElastic' => $this->t('easeInElastic'),
      'easeOutElastic' => $this->t('easeOutElastic'),
      'easeInOutElastic' => $this->t('easeInOutElastic'),
    ];

    $c = count($this->options['grouping']);
    // Add a form use group header field for every grouping, plus one.
    for ($i = 0; $i <= $c; $i++) {
      $grouping = !empty($this->options['grouping'][$i]) ? $this->options['grouping'][$i] : [];
      $grouping += ['use-grouping-header' => 0];
      $form['grouping'][$i]['use-grouping-header'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Use the group header as the Accordion header'),
        '#default_value' => $grouping['use-grouping-header'],
        '#description' => $this->t("If checked, the Group's header will be used to open/close the accordion."),
        '#states' => [
          'invisible' => [
            ':input[name="style_options[grouping][' . $i . '][field]"]' => ['value' => ''],
          ],
        ],
      ];
    }
    $form['grouping']['#prefix'] = '<div class="form-item">' . $this->t('<strong>IMPORTANT:</strong> The <em>first field</em> in order of appearance <em>will</em> be the one used as the "header" or "trigger" of the accordion action.') . '</div>';

    $form['disableifone'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Disable if only one result'),
      '#default_value' => $this->options['disableifone'],
      '#description' => $this->t("If set, the accordion will not be shown when there are less than 2 results."),
    ];

    $form['row-start-open'] = [
      '#type' => 'select',
      '#title' => $this->t('Row to display opened on start'),
      '#default_value' => $this->options['row-start-open'],
      '#description' => $this->t('Choose which row should start opened when the accordion first loads. If you want all to start closed, choose "None", and make sure to have "Allow for all rows to be closed" on below.'),
      '#options' => $rsopen_options,
    ];
    $form['collapsible'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Collapsible'),
      '#default_value' => $this->options['collapsible'],
      '#description' => $this->t('Whether all the sections can be closed at once. Allows collapsing the active section.'),
    ];
    $form['animated'] = [
      '#type' => 'select',
      '#title' => $this->t('Animation effect'),
      '#default_value' => $this->options['animated'],
      '#description' => $this->t('Choose what animation effect you would like to see, or "None" to disable it.'),
      '#options' => $animated_options,
    ];
    $form['animation_time'] = [
      '#type' => 'number',
      '#title' => $this->t('Animation time'),
      '#default_value' => $this->options['animation_time'],
      '#min' => 0,
      '#step' => 1,
      '#description' => $this->t('The animation duration in milliseconds'),
    ];
    $form['heightStyle'] = [
      '#type' => 'select',
      '#title' => $this->t('heightStyle'),
      '#default_value' => $this->options['heightStyle'],
      '#description' => $this->t('Controls the height of the accordion and each panel.'),
      '#options' => [
        'auto' => 'auto',
        'fill' => 'fill',
        'content' => 'content',
      ],
    ];
    $form['event'] = [
      '#type' => 'select',
      '#title' => $this->t('Event'),
      '#default_value' => $this->options['event'],
      '#description' => $this->t('The event on which to trigger the accordion.'),
      '#options' => [
        'click' => $this->t('Click'),
        'mouseover' => $this->t('Mouseover'),
      ],
    ];
    $form['use_header_icons'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Use header icons'),
      '#default_value' => $this->options['use_header_icons'],
      '#description' => $this->t('Icons to use for headers, matching an icon provided by the <a href="http://api.jqueryui.com/theming/icons/" target="_false">jQuery UI CSS Framework</a>. Uncheck to have no icons displayed.'),
    ];
    $show_if_use_header_icons = [
      'visible' => [
        ':input[name="style_options[use_header_icons]"]' => ['checked' => TRUE],
      ],
    ];
    $form['icon_header'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Closed row header icon'),
      '#default_value' => $this->options['icon_header'],
      '#states' => $show_if_use_header_icons,
    ];
    $form['icon_active_header'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Opened row header icon'),
      '#default_value' => $this->options['icon_active_header'],
      '#states' => $show_if_use_header_icons,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function preRender($result) {
    // No need do anything if we we have only one result and disableifone is
    // active.
    if ($this->options['disableifone'] == '1') {
      if (count($result) < 2) {
        return;
      }
    }

    $view_settings = [];
    $header_class = 'views-accordion-header';
    // This is used for triggering the creation of the accordions.
    // We append the dom_id so that multiple nested views with accordions work.
    $accordion_header_class = 'js-' . $header_class . '-' . $this->view->dom_id;

    $view_settings['usegroupheader'] = FALSE;
    foreach ($this->options['grouping'] as $group) {
      $view_settings['usegroupheader'] = $group['use-grouping-header'] == 1;
      // @TODO handle multiple grouping.
      break;
    }

    // Find out about the header field options.
    $fields = array_values($this->displayHandler->getOption('fields'));
    // Add header class to first not-excluded field.
    foreach ($fields as $field) {
      if (!isset($field['exclude']) || ($field['exclude'] == 0)) {
        // Make sure we are using a div for markup at least.
        if (empty($field['element_wrapper_type'])) {
          $this->view->field[$field['id']]->options['element_wrapper_type'] = 'div';
        }
        // Setup our wrapper class if not using group header.
        if (!$view_settings['usegroupheader']) {
          $header_wrapper_class = $header_class . ' ' . $accordion_header_class;
          // If the user configured its own class, set that up with our own
          // class.
          if (!empty($field['element_wrapper_class'])) {
            $header_wrapper_class = $field['element_wrapper_class'] . ' ' . $header_wrapper_class;
          }
          // Setup the view to use our processed wrapper class.
          $this->view->field[$field['id']]->options['element_wrapper_class'] = $header_wrapper_class;
        }
        break;
      }
    }

    $this->view->element['#attached']['library'][] = 'views_accordion/views_accordion.accordion';

    // Add the appropiate effect library if necessary.
    $effect = $this->options['animated'];
    if (($effect !== 'none') && ($effect !== 'swing') && ($effect !== 'linear')) {
      // jquery.ui.effects.core provides the easing effects.
      // It would be possible to integrate and load any other libraries here.
      $this->view->element['#attached']['library'][] = 'core/jquery.ui.effects.core';
    }

    // Prepare the JS settings.
    // We do it here so we don't have it run once every group.
    $view_settings['collapsible'] = $this->options['collapsible'];
    if ($this->options['row-start-open'] == 'random') {
      $view_settings['rowstartopen'] = 'random';
    }
    else {
      $view_settings['rowstartopen'] = ($this->options['row-start-open'] == 'none') ? FALSE : (int) $this->options['row-start-open'];
    }
    $view_settings['animated'] = ($this->options['animated'] == 'none') ? FALSE : $this->options['animated'];
    $view_settings['duration'] = ($this->options['animated'] == 'none') ? FALSE : $this->options['animation_time'];
    $view_settings['heightStyle'] = $this->options['heightStyle'];
    $view_settings['event'] = $this->options['event'];
    $view_settings['useHeaderIcons'] = $this->options['use_header_icons'];
    if ($this->options['use_header_icons']) {
      $view_settings['iconHeader'] = $this->options['icon_header'];
      $view_settings['iconActiveHeader'] = $this->options['icon_active_header'];
    }
    // The view display selector.
    // Set in stable & classy themes.
    $view_settings['display'] = '.js-view-dom-id-' . $this->view->dom_id;

    // The accordion header selector.
    $view_settings['header'] = '.' . $accordion_header_class;
    if ($view_settings['usegroupheader']) {
      // @TODO we cannot set a class for the grouping h3 apparently...
      $view_settings['header'] = '.js-views-accordion-group-header';
    }

    $this->view->element['#attached']['drupalSettings']['views_accordion'] = [$this->view->dom_id => $view_settings];
  }

  /**
   * {@inheritdoc}
   */
  public function render() {
    $rows = parent::render();
    $output = [
      '#theme' => $this->themeFunctions(),
      '#view' => $this->view,
      '#options' => $this->options,
      '#rows' => $rows,
    ];
    return $output;
  }

  /**
   * {@inheritdoc}
   */
  public function validate() {
    $errors = parent::validate();
    if (!$this->usesFields()) {
      $errors[] = $this->t('Views accordion requires Fields as row style');
    }

    foreach ($this->options['grouping'] as $group) {
      if (!$group['rendered'] && $group['use-grouping-header']) {
        $errors[] = $this->t('Views accordion requires "Use rendered output to group rows" enabled in order to use the group header as the Accordion header.');
      }
      // @TODO handle multiple grouping.
      break;
    }
    if ($this->options['collapsible'] !== 1 && $this->options['row-start-open'] === 'none') {
      $errors[] = $this->t('Setting "Row to display opened on start" to "None" requires "Collapsible" to be enabled.');
    }
    return $errors;
  }

}
