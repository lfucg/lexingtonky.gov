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

      var serverAuth = function(callback) {
        L.esri.post('https://devgisweb1.lexingtonky.gov/lfucggis/tokens/generateToken', {
          // lexKeys is set in a block visible to authenticated users
          username: lexKeys.finalOrderGIS.username,
          password: lexKeys.finalOrderGIS.password,
          f: 'json',
          expiration: 86400,
          client: 'referer',
          referer: window.location.origin
        }, callback);
      };

      // https://esri.github.io/esri-leaflet/examples/arcgis-server-auth.html
      var parcels;
      serverAuth(function(error, response) {
        parcels = L.esri.featureLayer({
          url: 'https://devgisweb1.lexingtonky.gov/lfucggis/rest/services/LFUCG_Apps/property/MapServer/1',
          token:  response.token
        });

        parcels.on('authenticationrequired', function (e) {
          serverAuth(function(error, response){
            e.authenticate(response.token);
          });
        });
      });

      $('.edit-field-parcel-lookup').on('blur', function (event) {
        var $pvaField = $(event.target);
        $pvaField.val($pvaField.val().trim());
        lookupParcel($pvaField.val());
      });


      var fieldValue = function(properties, keysForField) {
        var suffixes = {
          'CITYNAME': ',',
          'OWN1': "\n",
        };
        return _.compact(_.map(keysForField, function(key) {
          if (! properties[key]) { return; }
          var suffix = (suffixes[key] ? suffixes[key] : '');
          return properties[key] + suffix;
        })).join(' ');
      }

      var lookupParcel = function(parId) {
        var toOverwrite = {
          '#edit-field-address-0-value': ['LOC_ADRNO', 'LOC_ADRDIR', 'LOC_ADRSTR', 'LOC_ADRSUF', 'LOC_UNITNO', 'LOC_ZIP1'],
          '#edit-field-owners-address-0-value': ['OWN_AdrFull', 'CITYNAME', 'STATECODE', 'OWN_ZIP1', 'COUNTRY'],
          '#edit-field-person-charged-0-value': ['OWN1', 'OWN2'],
        };

        parcels.query()
          .where("PARID='" + parId + "'")
          .run(function(error, featureCollection) {
            Object.keys(toOverwrite).forEach(function(elId) {
              var keysForField = toOverwrite[elId];
              if ($(elId).val() === '') {
                var val = fieldValue(featureCollection.features[0].properties, keysForField);
                $(elId).val(val);
              }
            });
          });
      };
    }
  };
})(jQuery);
