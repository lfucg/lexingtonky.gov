(function() {
  let $ = jQuery;

  const apiCalls = [
    {
      id: 'trash',
      url: 'https://services1.arcgis.com/Mg7DLdfYcSWIaDnu/arcgis/rest/services/Waste_Management_Area/FeatureServer/0/query',
      label: 'Trash',
      fields: [
        'QUAD'
      ],
      field_keys: {
        A: 'Monday',
        B: 'Thursday',
        C: 'Tuesday',
        D: 'Friday',
        E: 'Daily',
      }
    },
    {
      id: 'political',
      url: 'https://services1.arcgis.com/Mg7DLdfYcSWIaDnu/arcgis/rest/services/Voting_Precinct/FeatureServer/0',
      label: 'Political',
      fields: [
        'NAME',
        'COUNCIL',
        'COUNREP',
        'SENREP',
        'LEGREP',
      ]
    },
    {
      id: 'police',
      url: 'https://services1.arcgis.com/Mg7DLdfYcSWIaDnu/arcgis/rest/services/Police_Zone_and_Sector/FeatureServer/0',
      label: 'Police',
      fields: [
        'PZONE'
      ],
      field_keys: {
        1: 'West Sector',
        2: 'Central Sector',
        3: 'East Sector',
      }
    },
    {
      id: 'zip',
      url: 'https://services1.arcgis.com/Mg7DLdfYcSWIaDnu/arcgis/rest/services/Zip_Code/FeatureServer/0',
      label: 'Zip',
      fields: [
        'ZIPCODE'
      ]
    },
    {
      id: 'neighborhood',
      url: 'https://services1.arcgis.com/Mg7DLdfYcSWIaDnu/arcgis/rest/services/Neighborhood_Association/FeatureServer/0',
      label: 'Neighborhood',
      fields: [
        'Assoc_Name'
      ]
    },
    {
      id: 'watershed',
      url: 'https://services1.arcgis.com/Mg7DLdfYcSWIaDnu/arcgis/rest/services/Watershed/FeatureServer/0',
      label: 'Watershed',
      fields: [
        'Watershed'
      ]
    },

  ]

  /*
  * requires:
  *   jquery
  *   jquery-ui.autocomplete
  *   esri-leaflet
  */

  $.LexingtonLookUpServices = function(options) {
    let $addressInput = options.$addressInput;


    let getDataForAddress = function(error, featureCollection, response) {
      let responseJson = error;
      let address = responseJson.candidates[0].location;

      apiCalls.map((call) => {
        let esriQuery = L.esri.query({ url: call.url });
        esriQuery
          .contains(L.latLng([address.y, address.x]))
          .returnGeometry(false)
          .run(function(error, featureCollection, response){
            let feature = featureCollection.features[0];
            call.fields.map((field) => {
              if (call.hasOwnProperty('field_keys')) {
                $(`.${call.id}`).html(`${call.field_keys[feature.properties[field]]} `);
              } else {
                $(`.${call.id}`).html(`${feature.properties[field]}`);
              }
            })
          });
      })

      if ($('.service').hasClass('hide')) {
        $('.service').toggleClass('hide');
        $('.service-label').toggleClass('open');
      }
      $addressInput.removeClass('loading');
    };

    $addressInput.removeClass('loading');

    $addressInput.autocomplete({
      source: function (request, response) {
        let lexington = "-84.6604156494,37.8454742432,-84.2827148438,38.2114067078";
        $.get("https://geocode.arcgis.com/arcgis/rest/services/World/GeocodeServer/suggest", {
          text: request.term,
          maxSuggestions: 5,
          searchExtent: lexington,
          f: 'json'
        }, function(data) {
          let suggestions = [];
          data.suggestions.forEach(function(suggestion) {
            // sometimes bounding box includes nearby cities
            if (suggestion.text.match('Lexington')) { suggestions.push(suggestion.text); }
          });
          response(suggestions);
        });
      },
      select: function( event, ui ) {
        $addressInput.addClass('loading');

        $.get("https://geocode.arcgis.com/arcgis/rest/services/World/GeocodeServer/findAddressCandidates", {
          outSr: 4326,
          forStorage: false,
          outFields: '*',
          maxLocations: 5,
          singleLine: ui.item.value,
          f: 'json'
        }, getDataForAddress);
      },
      minLength: 2
    });
  }

  // gets reinitialized in browse-columns.js when the navigation is updated
  $.LexingtonLookUpServices({
    $addressInput: $('.js-lex-address'),
  });
}());
