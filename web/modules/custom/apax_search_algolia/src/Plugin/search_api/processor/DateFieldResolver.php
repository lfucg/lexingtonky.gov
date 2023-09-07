<?php

declare(strict_types=1);

namespace Drupal\apax_search_algolia\Plugin\search_api\processor;

use DateTime;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\search_api\Datasource\DatasourceInterface;
use Drupal\search_api\Item\ItemInterface;
use Drupal\search_api\Processor\ProcessorPluginBase;
use Drupal\search_api\Processor\ProcessorProperty;

/**
 * Adds the parsed year to the indexed data.
 *
 * @SearchApiProcessor(
 *   id = "resolved_date",
 *   label = @Translation("Date"),
 *   description = @Translation("Adds the date as a shared field if there is no field_date to pull from."),
 *   stages = {
 *     "add_properties" = 20,
 *   },
 *   locked = true,
 *   hidden = true,
 * )
 */
class DateFieldResolver extends ProcessorPluginBase {

    /**
     * {@inheritdoc}
     */
    public function getPropertyDefinitions(DatasourceInterface $datasource = NULL): array {
        $properties = [];

        if (!$datasource) {
            $definition = [
                'label' => $this->t('Date'),
                'description' => $this->t('Adds the date as a shared field if there is no field_date to pull from.'),
                'type' => 'date',
                'processor_id' => $this->getPluginId(),
            ];
            $properties['resolved_date'] = new ProcessorProperty($definition);
        }

        return $properties;
    }

    /**
     * {@inheritdoc}
     */
    public function addFieldValues(ItemInterface $item): void {
        $entity = $item->getOriginalObject()->getValue();

        $date = NULL;
        if ($entity->bundle() == 'event') {
            $dateField = $entity->get('field_date_end');
        } elseif ($entity->bundle() == 'meeting') {
            $dateField = $entity->get('field_date_end')->isEmpty() ? $entity->get('field_date') : $entity->get('field_date_end');
        }
    
        if (isset($dateField) && !$dateField->isEmpty()) {
            // Get saved timestamp from a DateTimeFieldItemList field.
            $date_value = $dateField->value;
            $date_time = new DrupalDateTime($date_value, new \DateTimeZone('UTC'));
            // Get the Unix timestamp.
            $date = $date_time->getTimestamp();
        } else {
            // set the datetime field for the filter SO far in the future to avoid
            // filtering out these results
            $d = new DateTime('2100-12-31');
            $date = $d->getTimestamp();
        }

        $fields = $this->getFieldsHelper()
            ->filterForPropertyPath($item->getFields(), NULL, 'resolved_date');
        foreach ($fields as $field) {
            if (!$field->getDatasourceId()) {
                $field->addValue($date);
            }
        }
    }
}
