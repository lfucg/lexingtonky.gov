/**
 * @file
 * Provides additional javascript for managing the paragraph wysiwyg dialog.
 */

(function($) {
  'use strict';

  $('.paragraphs-wysiwyg-add-type .form-submit').click(function() {
    this.form.elements.namedItem('selected_bundle').value = this.attributes['data-paragraph-bundle'].value;
  });

  // When we are editing an entity that was already on the body copy, the Embed button doesn't update the changes.
  // This happens because when we open the second Modal Dialog, the first one loses the reference.
  // This is a little trick to reinstate the callback. Its not beautiful, but works beautifully.
  $(window).on('editor:dialogsave', function (e, values) {
    if (typeof window.ckeditorSaveCallback == 'function') {
      Drupal.ckeditor.saveCallback = window.ckeditorSaveCallback;
      values.attributes['rnd'] = Math.random();
      Drupal.ckeditor.saveCallback(values);
      delete window.ckeditorSaveCallback;
    }
  });

})(jQuery);
