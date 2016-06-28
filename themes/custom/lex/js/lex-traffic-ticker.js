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
      return '<li>' + closure.location + ' – ' + closure.impact + until + '</li>';
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
    $('.lex-traffic-scheduledClosures').html(closuresMarkup(results));
  };


  var markupIncident = function(incident) {
    return '<li>' + incident.direction + '. ' + incident.location + ' – ' + incident.issueDescription + '</li>';
  };

  var markupIncidents = function(incidents) {
    return '<ul>' +
      _.map(incidents, function(i) { return markupIncident(i) }).join('') +
    '</ul>';
  }

  var displayIncidents = function(results, errors, meta) {
    var html = '';
    var bySection = _.groupBy(results.data, function(r) {
      return r.sectionHeading;
    });

    _.each(bySection, function(incidents, section) {
      if (section !== '') {
        html += '<h2>' + section + '</h2>';
      }
      var withLocations = _.filter(incidents, function(r) { return r.location });
      var incidentTypes = _.groupBy(withLocations, function(i) { return i.incidentType; });
      html += _.map(incidentTypes, function(incidents, type) {
        return '<h3>' + type + '</h3>' + markupIncidents(incidents);
      }).join('');
    });

    $('.lex-traffic-incidents').html(html);
  };

  $.get("/traffic-incidents.csv", function(results, statusCode, req) {
    var u = moment(new Date(req.getResponseHeader('Last-modified')));
    $('.lex-traffic-lastUpdated').html(u.format('MM/DD/YYYY hh:mm:ss a'));
    displayIncidents(Papa.parse(results, { header: true }));
  });

  // $.get("/scheduled-closures.csv", function(results, statusCode, req) {
  //
  // });

  // Papa.parse("/scheduled-closures.csv", {
  //   download: true,
  //   header: true,
  //   complete: displayClosures,
  // });
}());
