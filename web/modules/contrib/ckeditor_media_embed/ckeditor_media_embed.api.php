<?php

/**
 * @file
 * Hooks provided by the CKEditor Media Embed Plugin module.
 */

use Drupal\Component\Utility\Html;

/**
 * @addtogroup hooks
 * @{
 */

/**
 * Alter the HTML of an embed object.
 *
 * @param object $embed
 *   The embed json decoded object as provided by Embed::getEmbedObject().
 */
function ckeditor_media_embed_ckeditor_media_embed_object_alter(&$embed) {
  $title_exists = (
    !empty($embed->title)
    && $title = Html::escape($embed->title)
  );

  if ($title_exists && $document = Html::load(trim($embed->html))) {
    if ($iframes = $document->getElementsByTagName('iframe')) {
      foreach ($iframes as $iframe) {
        $iframe->setAttribute('title', $title);
      }

      $embed->html = Html::serialize($document);
    }
  }
}

/**
 * @} End of "addtogroup hooks".
 */
