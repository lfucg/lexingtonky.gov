(function() {
  var $ = jQuery;
  var query = L.esri.query({
    url: "//maps.lexingtonky.gov/lfucggis/rest/services/planning/MapServer/0"
  });
  query.orderBy('Assoc_Name', 'DESC');
  query.run(function(error, featureCollection, response){
    var template = $('.js-lex-association-template');
    var associations = $('.js-lex-associations');

    var associationMarkup = function(properties) {
      return 'test';
    }

    featureCollection.features.forEach(function(association) {
      var newTemplate = template.clone();
      var id = 'association-' + association.properties['ID'];
      newTemplate.find('.js-accordion-control')
        .attr('aria-controls', id)
        .html(association.properties['Assoc_Name']);
      newTemplate.find('.lex-accordion-content')
        .html(associationMarkup(association.properties))
        .attr('id', id);
      associations.append(newTemplate);
    });
    $.LexingtonFilterBlock(document.getElementsByClassName('js-lex-filter-block')[0]);
  });
}());
