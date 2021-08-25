<?php

namespace Drupal\search_api\Plugin\search_api\processor;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\PluginFormInterface;
use Drupal\search_api\Plugin\PluginFormTrait;
use Drupal\search_api\Processor\ProcessorPluginBase;

/**
 * Adds a boost based on a number field value.
 *
 * @SearchApiProcessor(
 *   id = "number_field_boost",
 *   label = @Translation("Number field-based boosting"),
 *   description = @Translation("Adds a boost to indexed items based on the value of a numeric field."),
 *   stages = {
 *     "preprocess_index" = 0,
 *   }
 * )
 */
class NumberFieldBoost extends ProcessorPluginBase implements PluginFormInterface {

  use PluginFormTrait;

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration(): array {
    return [
      'boosts' => [],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state): array {
    $boost_factors = [
      '' => $this->t('Ignore'),
      '0.1' => '0.1',
      '0.2' => '0.2',
      '0.3' => '0.3',
      '0.5' => '0.5',
      '0.8' => '0.8',
      '1.0' => '1.0',
      '2.0' => '2.0',
      '3.0' => '3.0',
      '5.0' => '5.0',
      '8.0' => '8.0',
      '13.0' => '13.0',
      '21.0' => '21.0',
    ];
    $config = $this->configuration['boosts'];

    foreach ($this->index->getFields(TRUE) as $field_id => $field) {
      if (in_array($field->getType(), ['integer', 'decimal'])) {
        $form['boosts'][$field_id] = [
          '#type' => 'details',
          '#title' => $field->getLabel(),
        ];

        $default_boost = $config[$field_id]['boost_factor'] ?? '';
        if ($default_boost) {
          $default_boost = sprintf('%.1F', $default_boost);
        }
        $form['boosts'][$field_id]['boost_factor'] = [
          '#type' => 'select',
          '#title' => $this->t('Boost factor'),
          '#options' => $boost_factors,
          '#description' => $this->t('The boost factor the field value gets multiplied with. Setting it to 1.0 means using the field value as a boost as it is.'),
          '#default_value' => $default_boost,
        ];

        $form['boosts'][$field_id]['aggregation'] = [
          '#type' => 'select',
          '#title' => $this->t('Aggregation'),
          '#options' => [
            'max' => $this->t('maximum'),
            'min' => $this->t('minimum'),
            'avg' => $this->t('average'),
            'sum' => $this->t('sum'),
            'mul' => $this->t('product'),
            'first' => $this->t('use first value'),
          ],
          '#description' => $this->t('Select the method of aggregation to use in case the field has multiple values.'),
          '#default_value' => $config[$field_id]['aggregation'] ?? 'max',
          // @todo This shouldn't be dependent on the form array structure.
          //   Use the '#process' trick instead.
          '#states' => [
            'invisible' => [
              ":input[name=\"processors[number_field_boost][settings][boosts][$field_id][boost_factor]\"]" => [
                'value' => '',
              ],
            ],
          ],
        ];
      }
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    foreach ($values['boosts'] as $field_id => $settings) {
      if (!$settings['boost_factor']) {
        unset($values['boosts'][$field_id]);
      }
    }
    $form_state->setValues($values);
    $this->setConfiguration($values);
  }

  /**
   * {@inheritdoc}
   */
  public function preprocessIndexItems(array $items) {
    $boosts = $this->configuration['boosts'];

    /** @var \Drupal\search_api\Item\ItemInterface $item */
    foreach ($items as $item) {
      foreach ($boosts as $field_id => $settings) {
        if ($field = $item->getField($field_id)) {
          if ($values = $field->getValues()) {
            switch ($settings['aggregation']) {
              case 'min':
                $value = min($values);
                break;

              case 'avg':
                $value = array_sum($values) / count($values);
                break;

              case 'sum':
                $value = array_sum($values);
                break;

              case 'mul':
                $value = array_product($values);
                break;

              case 'first':
                $value = reset($values);
                break;

              case 'max':
              default:
                $value = max($values);
                break;

            }
            if ($value) {
              $item->setBoost($item->getBoost() * (double) $value * (double) $settings['boost_factor']);
            }
          }
        }
      }
    }
  }

}
