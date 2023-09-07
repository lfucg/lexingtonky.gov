<?php

declare(strict_types=1);

namespace Drupal\apax_search_algolia\Plugin\search_api\processor;

use Drupal\search_api\Datasource\DatasourceInterface;
use Drupal\search_api\Item\ItemInterface;
use Drupal\search_api\Processor\ProcessorPluginBase;
use Drupal\search_api\Processor\ProcessorProperty;

/**
 * Adds the parsed year to the indexed data.
 *
 * @SearchApiProcessor(
 *   id = "resolved_image",
 *   label = @Translation("image"),
 *   description = @Translation("Adds the image url for previews."),
 *   stages = {
 *     "add_properties" = 20,
 *   },
 *   locked = true,
 *   hidden = true,
 * )
 */
class ImageResolver extends ProcessorPluginBase {

    /**
     * {@inheritdoc}
     */
    public function getPropertyDefinitions(DatasourceInterface $datasource = NULL): array {
        $properties = [];

        if (!$datasource) {
            $definition = [
                'label' => $this->t('Image'),
                'description' => $this->t('Adds the preview image url.'),
                'type' => 'string',
                'processor_id' => $this->getPluginId(),
            ];
            $properties['resolved_image'] = new ProcessorProperty($definition);
        }

        return $properties;
    }

    /**
     * {@inheritdoc}
     */
    public function addFieldValues(ItemInterface $item): void {
        $entity = $item->getOriginalObject()->getValue();

        $image_url = '';
        if ($entity->hasField('field_image') && !$entity->get('field_image')->isEmpty()) {
            $media = $entity->get('field_image')->first();
            if ($media_entity = $media->get('entity')->getTarget()) {
                if ($image = $media_entity->get('field_media_image')->entity) {
                    if ($image_style = \Drupal::entityTypeManager()->getStorage('image_style')->load('responsive_1_1_1050w')) {
                        $image_url = $image_style->buildUrl($image->getFileUri());
                    }
                }
            }
        }


        $fields = $this->getFieldsHelper()
            ->filterForPropertyPath($item->getFields(), NULL, 'resolved_image');
        foreach ($fields as $field) {
            if (!$field->getDatasourceId()) {
                $field->addValue($image_url);
            }
        }
    }
}
