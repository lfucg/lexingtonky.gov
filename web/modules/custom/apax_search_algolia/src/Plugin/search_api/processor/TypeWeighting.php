<?php

namespace Drupal\apax_search_algolia\Plugin\search_api\processor;

use Drupal\search_api\Datasource\DatasourceInterface;
use Drupal\search_api\Item\ItemInterface;
use Drupal\search_api\Processor\ProcessorPluginBase;
use Drupal\search_api\Processor\ProcessorProperty;

/**
 * Adds the content and events type to the indexed data.
 *
 * @SearchApiProcessor(
 *   id = "type_weight",
 *   label = @Translation("Type Weight"),
 *   description = @Translation("Adds the item's type to the indexed data."),
 *   stages = {
 *     "add_properties" = 20,
 *   },
 *   locked = true,
 *   hidden = true,
 * )
 */
class TypeWeighting extends ProcessorPluginBase {

    /**
     * {@inheritdoc}
     */
    public function getPropertyDefinitions(DatasourceInterface $datasource = NULL) {
        $properties = [];

        if (!$datasource) {
            $definition = [
                'label' => $this->t('Type Weight'),
                'description' => $this->t('Type Weight for sorting purposes.'),
                'type' => 'integer',
                'processor_id' => $this->getPluginId(),
            ];
            $properties['type_weight'] = new ProcessorProperty($definition);
        }

        return $properties;
    }

    /**
     * {@inheritdoc}
     */
    public function addFieldValues(ItemInterface $item) {
        $entity = $item->getOriginalObject()->getValue();

        $weight = 0;
        $bundle = $entity->bundle();
        $type = ucwords(str_replace('_', ' ', $bundle));

        switch ($type) {
            case 'Page':
                $weight = 100;
                break;
            case 'Organization Page':
                $weight = 80;
                break;
            case 'Meeting':
                $weight = 70;
                break;
            case 'News Article':
            case 'Event':
                $weight = 60;
                break;
            case 'Board Commission':
                $weight = 50;
                break;
            case 'Landing Page':
            case 'Full Page Iframe':
                $weight = 20;
                break;
            default:
                $weight = $type;
                break;
        }

        // Set the field in the index.
        if ($weight) {
            $fields = $this->getFieldsHelper()
                ->filterForPropertyPath($item->getFields(), NULL, 'type_weight');
            foreach ($fields as $field) {
                if (!$field->getDatasourceId()) {
                    $field->addValue($weight);
                }
            }
        }
    }
}
