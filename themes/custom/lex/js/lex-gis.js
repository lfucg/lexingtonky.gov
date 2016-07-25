(function() {
  var $ = jQuery;
  $('article').html('lkjlkjjjjjk');
  // var map = L.map('map').setView([38.029, -84.4947], 13);
  // L.esri.basemapLayer("Streets").addTo(map);
  // var district = L.esri.featureLayer({
  //   where: 'DISTRICT=1',
  //   url: "http://maps.lexingtonky.gov/lfucggis/rest/services/political/MapServer/1",
  //   style: function () {
  //     return { color: "#70ca49", weight: 2 };
  //   }
  // }).addTo(map);
  var query = L.esri.query({
    url: "//maps.lexingtonky.gov/lfucggis/rest/services/planning/MapServer/0"
  });
  query.run(function(error, featureCollection, response){
    console.log('Found ' + featureCollection.features.length + ' features');

    var template = $('.js-lex-association-template');
    var associations = $('.js-lex-associations');

    featureCollection.features.forEach(function(assocation) {
      var newTemplate = template.clone()
      newTemplate.find('.js-accordion-control').html(assocation.properties['Assoc_Name']);
      associations.append(newTemplate);
    });
  });
}());
