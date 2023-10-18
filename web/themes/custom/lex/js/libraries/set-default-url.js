(($, Drupal) => {
    'use strict';

    Drupal.behaviors.defaultUrlValue = {
      attach: function (context, settings) {
        $(once('set-url', '#edit-field-feedback-url-0-value', context)).val(document.referrer);
      },
    };
})(jQuery, Drupal);
