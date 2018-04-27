(function() {
  var $ = jQuery;

  // could be passed as option eventually
  var handleDistrict = function(districtFeature){
    $('.js-lex-district-url').html('Council District ' + districtFeature.properties['DISTRICT']);
    $('.js-lex-district-url').prop('href', districtFeature.properties['URL']);
    $('.js-lex-district-member').html(districtFeature.properties['REP']);
    $('.js-lex-district-container').show();
  }

  /*
  * requires:
  *   jquery
  *   jquery-ui.autocomplete
  *   esri-leaflet
  */
  $.LexingtonGeocoder = function(options) {

    var $addressInput = options.$addressInput;
    var political = L.esri.query({url: 'https://maps.lexingtonky.gov/lfucggis/rest/services/political/MapServer/1'});

    var handleFindAddressResponse = function(error, featureCollection, response) {
        var responseJson = JSON.stringify(error);
        var address = JSON.parse(responseJson).locations[0].feature.geometry;

        political
          .contains(L.latLng([address.y, address.x]))
          .returnGeometry(false)
          .run(function(error, featureCollection, response){
            handleDistrict(featureCollection.features[0]);
          });
        $addressInput.removeClass('loading');
    };

    $addressInput.removeClass('loading');

    $addressInput.autocomplete({
      source: function (request, response) {
        var lexington = "-84.6604156494,37.8454742432,-84.2827148438,38.2114067078";
        $.get("https://geocode.arcgis.com/arcgis/rest/services/World/GeocodeServer/suggest", {
          text: request.term,
          maxSuggestions: 5,
          searchExtent: lexington,
          f: 'json'
        }, function(data) {
          var suggestions = [];
          // console.log(JSON.parse(data).suggestions);
          data.suggestions.forEach(function(suggestion) {
            // sometimes bounding box includes nearby cities
            if (suggestion.text.match('Lexington')) { suggestions.push(suggestion.text); }
          });
          response(suggestions);
        });
      },
      select: function( event, ui ) {
        $addressInput.addClass('loading');
        $.get("https://geocode.arcgis.com/arcgis/rest/services/World/GeocodeServer/find", {
          outSr: 4326,
          forStorage: false,
          outFields: '*',
          maxLocations: 5,
          text: ui.item.value,
          f: 'json'
        }, handleFindAddressResponse);
      },
      minLength: 2
    });
  }

  // gets reinitialized in browse-columns.js when the navigation is updated
  $.LexingtonGeocoder({
    $addressInput: $('.js-lex-district-address'),
  });
}());
