/**
 * @file
 * Paragraphs accordion script.
 */

(function ($, Drupal, window, document) {
  Drupal.behaviors.paragraphs_jquery_ui_accordion = {
    attach: function (context, settings) {
      var ids = drupalSettings.paragraphs_jquery_ui_accordion.ids;
      var autoscroll = parseInt(drupalSettings.paragraphs_jquery_ui_accordion.autoscroll);
      var active = parseInt(drupalSettings.paragraphs_jquery_ui_accordion.active);
      var autoscroll_offset = parseInt(drupalSettings.paragraphs_jquery_ui_accordion.autoscroll_offset);

      if (!autoscroll_offset) {
        autoscroll_offset = 0;
      }

      // Determine initial state of accordion paragraphs (from hash or first item).
      if (window.location.hash || active === 0) {
        var activeParagraph = false;
      } else {
        var activeParagraph = 0;
      }

      // Initialization of each accordion.
      $.each(ids, function( index, accordion_id ) {
        accordion_id = '#' + accordion_id;
        $(accordion_id, context).accordion({
          active: activeParagraph,
          collapsible: true,
          animated: 'slide',
          autoHeight: false,
          navigation: true,
          heightStyle: "content"
        });

        if (autoscroll === 1) {
          $(accordion_id, context).on( "accordionactivate", function( event, ui ) {
            var newHash = $(ui.newHeader).find('a').attr('href');
            autoScroll(newHash);
          });
        }

        // Open content that matches the hash.
        $( window ).on('hashchange', function() {
          activateParagraph(accordion_id);
        }).trigger('hashchange');
      });

      /**
       * AutoScroll function.
       *
       * @param newHash
       */
      function autoScroll(newHash) {
        if (newHash !== 'undefined' && newHash) {
          var target = $(newHash);
          $('html, body').animate({
            scrollTop: target.offset().top - autoscroll_offset
          }, 250);
          return false;
        }
      }

      /**
       * Helper function for activation accordion paragraphs from hash.
       *
       * @param accordion_id
       */
      function activateParagraph(accordion_id) {
        var hash = window.location.hash;
        if (hash) {
          var thash = hash.substring(hash.lastIndexOf('#'), hash.length);
          var paragraph = $(accordion_id).find('a[href*=\\'+ thash + ']').closest('h3');
          if (!$(paragraph).hasClass("ui-state-active")) {
            $(paragraph).trigger('click');
          }
        }
      }
    }
  };

}(jQuery, Drupal, this, this.document));
