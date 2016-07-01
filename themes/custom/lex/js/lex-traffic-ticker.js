(function() {
  var $ = jQuery;
  var baseUrl = 'http://traffic.erikschwartz.net';

  var closuresMarkup = function(results) {
    var convertTime = function(military) {
      return military > 12 ? (military - 12 + ' p.m.') : (military + ' a.m.');
    }

    var withLocations = _.filter(results.data, function(c) { return c.location });
    var timeRanges = _.groupBy(withLocations, function(closure) {
      return convertTime(closure.closureBegin) + ' – ' + convertTime(closure.closureEnd);
    });
    var markupClosure = function(closure) {
      var until = (closure.closedUntil !== '' ? ' Thru ' + closure.closedUntil : '');
      return '<li>' +
        (closure.isNew ? '<span class="lex-traffic-notice-highlight">New:</span> ' : '') +
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
    var header = sectionHeading(results.data[0]);
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

  var sectionHeading = function(firstRow) {
    return (firstRow.sectionHeading ?
      '<h2>' + firstRow.sectionHeading + '</h2>' :
      'Nothing');
  }

  var displayIncidents = function(incidents) {
    var html = sectionHeading(incidents[0]);
    var withLocations = _.filter(incidents, function(r) { return r.location });
    var incidentTypes = _.groupBy(withLocations, function(i) { return i.incidentType; });
    html += _.map(incidentTypes, function(incidents, type) {
      return '<h3>' + type + '</h3>' + markupIncidents(incidents);
    }).join('');

    $('.lex-traffic-incidents').html(html);
  };

  var markupImpact = function(impact) {
    return '<li>' +
      impact.event + ' ' +
      impact.timeBegin +
      (impact.timeEnd ? ' – ' + impact.timeEnd : '') +
      '</li>';
  }

  var markupImpacts = function(impacts) {
    return '<ul>' +
      _.map(impacts, function(i) { return markupImpact(i) }).join('') +
    '</ul>';
  };

  var displayWeekendImpacts = function(results) {
    var impactRows = results.data;
    var html = sectionHeading(impactRows[0]);
    var withEvent = _.filter(impactRows, function(i) { return i.event });
    var byDay = _.groupBy(withEvent, function(i) { return i.day; });
    html += _.map(byDay, function(impacts, day) {
      return '<h3>' + day + '</h3>' + markupImpacts(impacts);
    }).join('');

    $('.lex-traffic-weekendImpacts').html(html);
  }

  var isWeekend = function() {
    // grabbed this wholesale from old website
    var curDate=new Date();
    var curDay=curDate.getDay();
    var weekendCutOver=new Date((curDate.toLocaleDateString()) + " 15:30:00" );
    var weekdayCutOver=new Date((curDate.toLocaleDateString()) + " 20:00:00" );
    if(((curDay==0)&&(curDate<weekdayCutOver))||(curDay==6)||((curDay==5)&&(curDate>=weekendCutOver)))
    {
      return true;
    }
  }

  var displayWeekdayOrWeekend = function() {
    if (isWeekend()) {
      Papa.parse(baseUrl + "/weekend-impacts.csv", {
        download: true,
        header: true,
        complete: displayWeekendImpacts,
      });
    } else {
      Papa.parse(baseUrl + "/scheduled-closures.csv", {
        download: true,
        header: true,
        complete: displayClosures,
      });
    }
  }

  window.refreshTicker = function() {
    $.get(baseUrl + "/traffic-incidents.csv", function(results, statusCode, req) {
      var updated = moment(new Date(req.getResponseHeader('Last-modified')));
      $('.lex-traffic-lastUpdated').html(updated.format('MM/DD/YYYY hh:mm:ss a'));

      var incidents = Papa.parse(results, { header: true }).data;
      displayIncidents(incidents);
    });
    displayWeekdayOrWeekend();
  }

  if (window.location.search === '?just-a-test=true') {
    window.refreshTicker();
    setInterval(refreshTicker, 60000);
  }
}());
