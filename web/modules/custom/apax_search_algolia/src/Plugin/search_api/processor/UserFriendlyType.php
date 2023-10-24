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
 *   id = "user_friendly_type",
 *   label = @Translation("User Friendly Type"),
 *   description = @Translation("Adds the item's type to the indexed data."),
 *   stages = {
 *     "add_properties" = 20,
 *   },
 *   locked = true,
 *   hidden = true,
 * )
 */
class UserFriendlyType extends ProcessorPluginBase {

    /**
     * {@inheritdoc}
     */
    public function getPropertyDefinitions(DatasourceInterface $datasource = NULL) {
        $properties = [];

        if (!$datasource) {
            $definition = [
                'label' => $this->t('User Friendly Type'),
                'description' => $this->t('User Friendly type field for separate datasets.'),
                'type' => 'string',
                'processor_id' => $this->getPluginId(),
            ];
            $properties['user_friendly_type'] = new ProcessorProperty($definition);
        }

        return $properties;
    }

    /**
     * {@inheritdoc}
     */
    public function addFieldValues(ItemInterface $item) {
        $entity = $item->getOriginalObject()->getValue();

        $user_type = NULL;
        $bundle = $entity->bundle();
        $type = ucwords(str_replace('_', ' ', $bundle));

        switch ($type) {
            case 'Landing Page':
            case 'Full Page Iframe':
            case 'Organization Page':
            case 'Page':
                $user_type = 'Page';
                break;

            case 'Board Commission':
                $user_type = 'Boards and Commissions';
                break;

            case 'News Article':
                $user_type = 'News';
                break;
            case 'Meeting':
                $user_type = 'Meeting';
                break;
            case 'Event':
                $user_type = 'Event';
                break;

            default:
                $user_type = $type;
                break;
        }

        // Set the field in the index.
        if ($user_type) {
            $fields = $this->getFieldsHelper()
                ->filterForPropertyPath($item->getFields(), NULL, 'user_friendly_type');
            foreach ($fields as $field) {
                if (!$field->getDatasourceId()) {
                    $field->addValue($user_type);
                }
            }
        }
    }
}
