(($, Drupal) => {
    'use strict';

    Drupal.behaviors.defaultUrlValue = {
      attach: function (context, settings) {
        $('#edit-field-feedback-url-0-value', context).once('set-url').val(document.referrer);
      },
    };
})(jQuery, Drupal);
