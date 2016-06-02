(function() {
  var $ = jQuery;

  // could be passed as option eventually
  var handleDistrict = function(featureCollection){
    var district = featureCollection.features[0];
    if (district) {
      $('.js-lex-district-number').html(district.properties['DISTRICT']);
      // $('.js-lex-district-url').prop('href', district.properties['URL']);
      // hard code to /council-district-x until GIS changes links to new site
      $('.js-lex-district-url').prop('href', '/council-district-' + district.properties['DISTRICT']);
      $('.js-lex-district-member').html(district.properties['REP']);
      $('.js-lex-district-container').show();
    }
  }

  /*
  * requires:
  *   jquery
  *   jquery-ui.autocomplete
  *   esri-leaflet
  */
  $.LexingtonGeocoder = function(options) {
    var $addressInput = options.$addressInput;

    var handleFindAddressResponse = function(error, featureCollection, response) {
      var query = L.esri.query({
        url: "//maps.lexingtonky.gov/lfucggis/rest/services/political/MapServer/1",
      }).intersects(featureCollection.features[0]);
      query.fields(['DISTRICT', 'REP', 'URL']);
      query.run(function(error, featureCollection, response) {
        handleDistrict(featureCollection);
        $addressInput.removeClass('loading');
      });
    };

    $addressInput.autocomplete({
      source: function (request, response) {
        $.get("//lexington-geocode-proxy.herokuapp.com/maps.lexingtonky.gov/mapit/Map/GetSearchSuggestions", {
          term: request.term
        }, response);
      },
      select: function( event, ui ) {
        $addressInput.addClass('loading');
        var finder = L.esri.find({
          url: '//maps.lexingtonky.gov/lfucggis/rest/services/addresses/MapServer/',
        }).text(ui.item.value)
          .layers([0])
          .run(handleFindAddressResponse);
      },
      minLength: 2
    });
  }

  // gets reinitialized in browse-columns.js when the navigation is updated
  $.LexingtonGeocoder({
    $addressInput: $('.js-lex-district-address'),
  });
}());
