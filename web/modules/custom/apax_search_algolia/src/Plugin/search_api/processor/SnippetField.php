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
 *   id = "snippet_field",
 *   label = @Translation("Snippet Field"),
 *   description = @Translation("Adds a bit of text for snippetting in search results"),
 *   stages = {
 *     "add_properties" = 20,
 *   },
 *   locked = true,
 *   hidden = true,
 * )
 */
class SnippetField extends ProcessorPluginBase {

    /**
     * {@inheritdoc}
     */
    public function getPropertyDefinitions(DatasourceInterface $datasource = NULL): array {
        $properties = [];

        if (!$datasource) {
            $definition = [
                'label' => $this->t('Snippet Field'),
                'description' => $this->t('Adds a bit of text for snippetting in search results'),
                'type' => 'string',
                'processor_id' => $this->getPluginId(),
            ];
            $properties['snippet_field'] = new ProcessorProperty($definition);
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
            case 'board_commission':
            case 'page':
                $field_value = $entity->get('field_page_overview')->getValue();
                $snippet = $field_value[0]['value'];
                break;
            case 'organization_page':
            case 'news_article':
            case 'meeting':
            case 'landing_page':
            case 'full_page_iframe':
            case 'event':
                $field_value = $entity->get('body')->getValue();
                $snippet = $field_value[0]['value'];
                break;

            default:
                $snippet = NULL;
                break;
        }

        if (!empty($snippet)) {
            $fields = $this->getFieldsHelper()
                ->filterForPropertyPath($item->getFields(), NULL, 'snippet_field');
            foreach ($fields as $field) {
                if (!$field->getDatasourceId()) {
                    $field->addValue(trim(html_entity_decode(strip_tags($snippet))));
                }
            }
        }
    }
}
