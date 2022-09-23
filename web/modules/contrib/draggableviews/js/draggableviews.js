/**
 * @file
 * draggableviews.js
 *
 * Defines the behaviors needed for draggableviews integration.
 */

(function ($, Drupal) {
  Drupal.behaviors.draggableviewsWeights = {
    attach: function (context, settings) {
      if ($('.draggableviews-weight').length) {
        $('.draggableviews-weight').each(function(i, obj) {
          $(this).attr('value', i);
        });
      }
    }
  };
})(jQuery, Drupal);
