(function() {
  var $ = jQuery;

  var mailTo = function(email) {
    return '<a href="mailto:' + email + '"">' + email + '</a>';
  }
  var link = function(url) {
    return '<a href="http://' + url + '"">' + url + '</a>';
  }

  var injectFieldValues = function(properties, $template) {
    $template.find('*[data-esri-field-template]').each(function(index, fieldTemplate) {
      var fieldName = $(fieldTemplate).data('esri-field-template');
      var processFnc;

      if (fieldName.match('Email')) {
        processFnc = mailTo;
      } else if (fieldName.match('Website')) {
        processFnc = link;
      }

      if (properties[fieldName] && processFnc) {
        html = processFnc(properties[fieldName]);
      } else {
        html = properties[fieldName];
      }

      $(fieldTemplate).html(html);
    });
  }

  var appendAssociation = function(associations, association, template) {
    var id = 'association-' + association.properties['ID'];

    // Check if the association is already in the container by searching for its ID
    if (!associations.find('#' + id).length) {
      var newTemplate = template.clone();
      newTemplate.find('.js-accordion-control')
        .attr('aria-controls', id)
        .html(association.properties['Assoc_Name']);
      injectFieldValues(
        association.properties,
        newTemplate.find('.lex-accordion-content')
          .attr('id', id));
      associations.append(newTemplate);
    }
  }

  var query = L.esri.query({
    url: "//maps.lexingtonky.gov/lfucggis/rest/services/planning/MapServer/0"
  });
  query.orderBy('Assoc_Name', 'DESC');
  query.run(function(error, featureCollection, response){
    var template = $('.js-lex-association-template');
    var associations = $('.js-lex-associations');

    featureCollection.features.forEach(function(association) {
      appendAssociation(associations, association, template);
    });

    $.LexingtonFilterBlock(document.getElementsByClassName('js-lex-filter-associations')[0]);
  });
}());
