/**
 * @file
 * Javascript for Field Example.
 */

/**
 * Uses PVANUM to populate address field
 */
(function ($) {

  'use strict';

  Drupal.behaviors.field_example_colorpicker = {
    attach: function () {
      if ($('#edit-title-0-value').val() === '') {
        $('#edit-title-0-value').val('Final order');
      }

      var parcels = L.esri.featureLayer({
        url: 'http://maps.lexingtonky.gov/lfucggis/rest/services/parcels/MapServer/0',
      });

      $('.edit-field-example-colorpicker').on('blur', function (event) {
        var $pvaField = $(event.target);
        $pvaField.val($pvaField.val().trim());

        parcels.query()
          .where("PVANUM='" + $pvaField.val() + "'")
          .run(function(error, featureCollection) {
            $('#edit-field-address-0-value').val(featureCollection.features[0].properties['ADDRESS']);
            $('#edit-field-owners-address-0-value').val(featureCollection.features[0].properties['ADDRESS']);
          });
      });
    }
  };
})(jQuery);
