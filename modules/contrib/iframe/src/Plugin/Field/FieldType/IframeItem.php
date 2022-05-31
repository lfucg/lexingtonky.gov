<?php

namespace Drupal\iframe\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\Component\Utility\Random;

/**
 * Plugin implementation of the 'Iframe' field type.
 *
 * @FieldType(
 *   id = "iframe",
 *   label = @Translation("Iframe"),
 *   description = @Translation("The Iframe module defines an iframe field type for the Field module. Further definable are attributes for styling the iframe, like: URL, width, height, title, headerlevel, class, frameborder, scrolling and transparency."),
 *   default_widget = "iframe_urlwidthheight",
 *   default_formatter = "iframe_default"
 * )
 */
class IframeItem extends FieldItemBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultFieldSettings() {
    return [
      'title' => NULL,
      'headerlevel' => NULL,
      'class' => NULL,
      'height' => NULL,
      'width' => NULL,
      'frameborder' => NULL,
      'scrolling' => NULL,
      'transparency' => NULL,
      'tokensupport' => NULL,
      'allowfullscreen' => NULL,
    ] + parent::defaultFieldSettings();
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    // url as 'string' for token support. Validation of url will occur later
    $properties['url'] = DataDefinition::create('string')
      ->setLabel(t('URL'));

    $properties['title'] = DataDefinition::create('string')
      ->setLabel(t('Title text'));

    $properties['headerlevel'] = DataDefinition::create('string')
      ->setLabel(t('Header Level'));

    $properties['width'] = DataDefinition::create('string')
      ->setLabel(t('Width'));

    $properties['height'] = DataDefinition::create('string')
      ->setLabel(t('Height'));

    $properties['class'] = DataDefinition::create('string')
      ->setLabel(t('Css class'));

    $properties['frameborder'] = DataDefinition::create('string')
      ->setLabel(t('Frameborder'));

    $properties['scrolling'] = DataDefinition::create('string')
      ->setLabel(t('Scrolling'));

    $properties['transparency'] = DataDefinition::create('string')
      ->setLabel(t('Transparency'));

    $properties['tokensupport'] = DataDefinition::create('string')
      ->setLabel(t('Token support'));

    $properties['allowfullscreen'] = DataDefinition::create('string')
      ->setLabel(t('Allow fullscreen'));

    return $properties;
  }

  /**
   * Implements hook_field_schema().
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    return [
      'columns' => [
        'url' => [
          'description' => 'The URL of the iframe.',
          'type' => 'varchar',
          'length' => 2048,
          'not null' => FALSE,
          'sortable' => TRUE,
          'default' => '',
        ],
        'title' => [
          'description' => 'The iframe title text.',
          'type' => 'varchar',
          'length' => 255,
          'not null' => FALSE,
          'sortable' => TRUE,
          'default' => '',
        ],
        'headerlevel' => [
          'description' => 'Header level for accessibility, defaults to "h3".',
          'type' => 'int',
          'size' => 'tiny',
          'not null' => TRUE,
          'default' => 3,
        ],
        'class' => [
          'description' => 'When output, this iframe will have this CSS class attribute. Multiple classes should be separated by spaces.',
          'type' => 'varchar',
          'length' => '255',
          'not null' => FALSE,
          'default' => '',
        ],
        'width' => [
          'description' => 'The iframe width.',
          'type' => 'varchar',
          'length' => 4,
          'not null' => FALSE,
          'default' => '600',
        ],
        'height' => [
          'description' => 'The iframe height.',
          'type' => 'varchar',
          'length' => 4,
          'not null' => FALSE,
          'default' => '800',
        ],
        'frameborder' => [
          'description' => 'Frameborder is the border around the iframe. Most people want it removed, so the default value for frameborder is zero (0), or no border.',
          'type' => 'int',
          'size' => 'tiny',
          'not null' => TRUE,
          'default' => 0,
        ],
        'scrolling' => [
          'description' => 'Scrollbars help the user to reach all iframe content despite the real height of the iframe content. Please disable it only if you know what you are doing.',
          'type' => 'varchar',
          'length' => 4,
          'not null' => TRUE,
          'default' => 'auto',
        ],
        'transparency' => [
          'description' => 'Allow transparency per CSS in the outer iframe tag. You have to set background-color:transparent in your iframe body tag too!',
          'type' => 'int',
          'size' => 'tiny',
          'not null' => TRUE,
          'default' => 0,
        ],
        'tokensupport' => [
          'description' => 'Are tokens allowed for users to use in title or URL field?',
          'type' => 'int',
          'size' => 'tiny',
          'not null' => TRUE,
          'default' => 0,
        ],
        'allowfullscreen' => [
          'description' => 'Allow fullscreen for iframe. The iframe can activate fullscreen mode by calling the requestFullscreen() method.',
          'type' => 'int',
          'size' => 'tiny',
          'not null' => TRUE,
          'default' => 0,
        ],
      ],
      'indexes' => [
        'url' => ['url'],
      ],
    ];
  }

  /**
   * Global field settings for iframe field.
   *
   * In contenttype-field-settings "Manage fields" -> "Edit"
   * admin/structure/types/manage/CONTENTTYPE/fields/node.CONTENTTYPE.FIELDNAME.
   */
  public function fieldSettingsForm(array $form, FormStateInterface $form_state) {
    $element = [];
    $settings = $this->getSettings() + self::defaultFieldSettings();
    // \iframe_debug(4, __METHOD__ . " settings", $settings);
    $element['class'] = [
      '#type' => 'textfield',
      '#title' => $this->t('CSS Class'),
      // ''
      '#default_value' => $settings['class'],
    ];

    $element['headerlevel'] = [
      '#type' => 'select',
      '#title' => $this->t('Header Level'),
      // '0'
      '#default_value' => $settings['headerlevel'] ?? 3,
      '#options' => [
        '1' => $this->t('h1'),
        '2' => $this->t('h2'),
        '3' => $this->t('h3'),
        '4' => $this->t('h4'),
      ],
    ];

    $element['frameborder'] = [
      '#type' => 'radios',
      '#title' => $this->t('Frameborder'),
      // '0'
      '#default_value' => $settings['frameborder'],
      '#options' => [
        '0' => $this->t('No frameborder'),
        '1' => $this->t('Show frameborder'),
      ],
    ];

    $element['scrolling'] = [
      '#type' => 'radios',
      '#title' => $this->t('Scrolling'),
      // 'auto'
      '#default_value' => $settings['scrolling'],
      '#options' => [
        'auto' => $this->t('Automatic'),
        'no' => $this->t('Disabled'),
        'yes' => $this->t('Enabled'),
      ],
    ];

    $element['transparency'] = [
      '#type' => 'radios',
      '#title' => $this->t('Transparency'),
      // '0'
      '#default_value' => $settings['transparency'],
      '#options' => [
        '0' => $this->t('No transparency'),
        '1' => $this->t('Allow transparency'),
      ],
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

    $element['tokensupport'] = [
      '#type' => 'radios',
      '#title' => $this->t('Token Support'),
      // '0'
      '#default_value' => $settings['tokensupport'],
      '#options' => [
        '0' => $this->t('No tokens allowed'),
        '1' => $this->t('Tokens only in title field'),
        '2' => $this->t('Tokens for title and URL field'),
      ],
      '#description' => $this->t('Are tokens allowed for users to use in title or URL field?'),
    ];
    if (!\Drupal::moduleHandler()->moduleExists('token')) {
      $element['tokensupport']['#description'] .= ' ' . t('Attention: Token module is not currently enabled!');
    }

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public static function generateSampleValue(FieldDefinitionInterface $field_definition) {
    // Set of possible top-level domains.
    $tlds = ['com', 'net', 'gov', 'org', 'edu', 'biz', 'info'];
    // Set random length for the domain name.
    $domain_length = mt_rand(7, 15);
    $random = new Random();

    switch ($field_definition->getSetting('title')) {
      case DRUPAL_DISABLED:
        $values['title'] = '';
        break;

      case DRUPAL_REQUIRED:
        $values['title'] = $random->sentences(4);
        break;

      case DRUPAL_OPTIONAL:
        // In case of optional title, randomize its generation.
        $values['title'] = mt_rand(0, 1) ? $random->sentences(4) : '';
        break;
    }
    $values['url'] = 'https://www.' . $random->word($domain_length) . '.' . $tlds[mt_rand(0, (count($tlds) - 1))];
    return $values;
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    $value = $this->get('url')->getValue();
    return $value === NULL || $value === '';
  }

  /**
   * {@inheritdoc}
   */
  public static function mainPropertyName() {
    return 'url';
  }

  /**
   * Get token support setting.
   */
  public function getTokenSupport() {
    $value = $this->getSetting('tokensupport');
    $value = empty($value) ? 0 : (int) $value;
    return $value;
  }

}
