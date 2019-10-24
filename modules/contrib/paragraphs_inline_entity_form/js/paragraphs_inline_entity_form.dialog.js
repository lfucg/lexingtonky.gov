/**
 * @file
 * Provides additional javascript for managing the paragraph wysiwyg dialog.
 */

(function($) {
  'use strict';

  $('.paragraphs-wysiwyg-add-type .form-submit').click(function() {
    this.form.elements.namedItem('selected_bundle').value = this.attributes['data-paragraph-bundle'].value;
  });

})(jQuery);
