/**
 * @file
 * Javascript for Field Example.
 */

/**
 * Uses PVANUM to populate address field
 */
(function ($) {

  'use strict';

  Drupal.behaviors.parcel_lookup = {
    attach: function () {
      if ($('#edit-title-0-value').val() === '') {
        $('#edit-title-0-value').val('Final order');
      }

      var parcels = L.esri.featureLayer({
        url: 'https://maps.lexingtonky.gov/lfucggis/rest/services/parcels/MapServer/0',
      });

      debugger
      $('.edit-field-parcel-lookup').on('blur', function (event) {
        var $pvaField = $(event.target);
        var toOverwrite = {
          '#edit-field-address-0-value': 'ADDRESS',
          '#edit-field-owners-address-0-value': 'ADDRESS'
        };

        $pvaField.val($pvaField.val().trim());

        parcels.query()
          .where("PVANUM='" + $pvaField.val() + "'")
          .run(function(error, featureCollection) {
            Object.keys(toOverwrite).forEach(function(elId) {
              var addressKey = toOverwrite[elId];
              if ($(elId).val() === '') {
                $(elId).val(featureCollection.features[0].properties[addressKey]);
              }
            });
          });
      });
    }
  };
})(jQuery);
