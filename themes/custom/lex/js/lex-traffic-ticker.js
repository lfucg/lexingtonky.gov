(function() {
  var $ = jQuery;

  var closuresMarkup = function(results) {
    var convertTime = function(military) {
      return military > 12 ? (military - 12 + ' p.m.') : (military + ' a.m.');
    }
    var timeRanges = _.groupBy(results.data, function(range) {
      return convertTime(range.closureBegin) + ' – ' + convertTime(range.closureEnd);
    });
    var markupClosure = function(closure) {
      var until = (closure.closedUntil !== '' ? ' Thru ' + closure.closedUntil : '');
      var classes = closure.isNew ? 'class="lex-traffic-notice lex-traffic-notice-info"' : '';
      return '<li ' + classes + '>' +
        (closure.isNew ? '<strong>New:</strong> ' : '') +
        closure.location + ' – ' + closure.impact + until + '</li>';
    }
    var markupRange = function(closures, range) {
      return '<h3>Closures scheduled from ' + range + '</h3>' +
      '<ul>' +
        _.map(closures, function(c) { return markupClosure(c) }).join('') +
      '</ul>';
    }
    return _.map(timeRanges, markupRange).join('');
  };

  var displayClosures = function(results) {
    var header = '<h2>Closures for ' + moment(new Date()).format('dddd, MMMM D') + '</h2>';
    $('.lex-traffic-scheduledClosures').html(header + closuresMarkup(results));
  };


  var markupIncident = function(incident) {
    var classes = incident.displayAlert ? 'class="lex-traffic-notice lex-traffic-notice-alert"' : '';
    return '<li ' + classes + '>' +
      (incident.direction ? incident.direction + '. ' : '') +
      incident.location +
      (incident.description ? ' – ' + incident.description : '') +
      '</li>';
  };

  var markupIncidents = function(incidents) {
    return '<ul>' +
      _.map(incidents, function(i) { return markupIncident(i) }).join('') +
    '</ul>';
  }

  var incidentHeader = function(headerRow) {
    return (headerRow.pageHeading ?
      '<h2>' + headerRow.pageHeading + '</h2>' :
      '');
  }
  var displayIncidents = function(headerRow, incidents) {
    var html = incidentHeader(headerRow);
    var withLocations = _.filter(incidents, function(r) { return r.location });
    var incidentTypes = _.groupBy(withLocations, function(i) { return i.incidentType; });
    html += _.map(incidentTypes, function(incidents, type) {
      return '<h3>' + type + '</h3>' + markupIncidents(incidents);
    }).join('');

    $('.lex-traffic-incidents').html(html);
  };

  $.get("/traffic-incidents.csv", function(results, statusCode, req) {
    var updated = moment(new Date(req.getResponseHeader('Last-modified')));
    $('.lex-traffic-lastUpdated').html(updated.format('MM/DD/YYYY hh:mm:ss a'));

    var incidentFile = results.split('beginIncidents');
    var headerRow = Papa.parse(incidentFile[0], { header: true }).data[0];
    var incidents = Papa.parse(incidentFile[1], { header: true }).data;
    displayIncidents(headerRow, incidents);
  });

  Papa.parse("/scheduled-closures.csv", {
    download: true,
    header: true,
    complete: displayClosures,
  });
}());
