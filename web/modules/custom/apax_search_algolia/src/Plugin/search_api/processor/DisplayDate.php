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
 *   id = "display_date",
 *   label = @Translation("Display Date"),
 *   description = @Translation("Adds a human readable date to display with search results."),
 *   stages = {
 *     "add_properties" = 20,
 *   },
 *   locked = true,
 *   hidden = true,
 * )
 */
class DisplayDate extends ProcessorPluginBase {

    /**
     * {@inheritdoc}
     */
    public function getPropertyDefinitions(DatasourceInterface $datasource = NULL): array {
        $properties = [];

        if (!$datasource) {
            $definition = [
                'label' => $this->t('Display Date'),
                'description' => $this->t('Adds a human readable date to display with search results.'),
                'type' => 'date',
                'processor_id' => $this->getPluginId(),
            ];
            $properties['display_date'] = new ProcessorProperty($definition);
        }

        return $properties;
    }

    /**
     * {@inheritdoc}
     */
    public function addFieldValues(ItemInterface $item): void {
        $entity = $item->getOriginalObject()->getValue();
        $bundle = $entity->bundle();

        switch ($bundle) {
            case 'page':
            case 'organization_page':
            case 'landing_page':
            case 'full_page_iframe':
            case 'board_commission':
                $timestamp = $entity->getChangedTime();
                break;

            case 'news_article':
                $timestamp = $entity->getCreatedTime();
                break;
            case 'meeting':
            case 'event':
                $timestamp = $entity->get('field_date')->value;
                break;

            default:
                $timestamp = NULL;
                break;
        }

        if (isset($timestamp)) {
            $fields = $this->getFieldsHelper()
                ->filterForPropertyPath($item->getFields(), NULL, 'display_date');
            foreach ($fields as $field) {
                if (!$field->getDatasourceId()) {
                    $field->addValue($timestamp);
                }
            }
        }
    }
}
