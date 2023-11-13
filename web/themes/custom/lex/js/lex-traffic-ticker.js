lexTicker = function () {
  var _this = {};

  var sectionHeading = function(heading) {
    return (heading ? '<h2>' + heading + '</h2>' : '');
  };

  var markupClosure = function(closure) {
    var until = (closure.closedUntil !== '' ? ' Thru ' + closure.closedUntil : '');
    return '<li>' +
      (closure.isNew ? '<span class="lex-traffic-notice-highlight">New:</span> ' : '') +
      closure.location +
      (closure.impact + until === '' ? '' : ' – ' + closure.impact + until) +
      '</li>';
  };

  var convertTime = function(military) {
    /* sometimes is a string like 24 Hrs/Day */
    if (military.match(/^\d+$/)) {
      return military > 12 ? (military - 12 + ' p.m.') : (military + ' a.m.');
    } else {
      return military;
    }
  };

  var markupImpact = function(impact) {
    return '<li>' +
      impact.event + ' ' +
      impact.timeBegin +
      (impact.timeEnd ? ' – ' + impact.timeEnd : '') +
      '</li>';
  };

  var markupRows = function(rows, rowMarkupFnc) {
    return '<ul>' +
      _.map(rows, function(i) { return rowMarkupFnc(i); }).join('') +
    '</ul>';
  };

  var groupBy = function(collection, iteratee) {
    var grouped = {};
    collection.forEach(function (item) {
      var key = iteratee(item);
      if (!grouped[key]) {
        grouped[key] = [];
      }
      grouped[key].push(item);
    });
    return grouped;
  }

  var markupSection = function(options) {
    var byHeading = _this.groupByHeading(options.rows);
    return Object.entries(byHeading)
    .map(function ([heading, row]) {
      var filtered = row.filter(options.filter);
      var grouped = groupBy(filtered, options.group);
      var body = Object.values(grouped).map(options.markupBody).join('');
      return sectionHeading(heading) + body;
    })
    .join('');
  };

  var markupIncident = function(incident) {
    var classes = incident.displayAlert ? 'class="lex-traffic-notice lex-traffic-notice-alert"' : '';
    return '<li ' + classes + '>' +
      incident.location +
      (incident.description ? ' – ' + incident.description : '') +
      '</li>';
  };

  _this.groupByHeading = function(rows) {
    var byHeadings = {};
    var currHeading = "";
    rows.forEach(function(row) {
      if (row.sectionHeading !== "" && currHeading !== row.sectionHeading) {
        currHeading = row.sectionHeading;
      }
      if (! byHeadings[currHeading]) {
        byHeadings[currHeading] = [];
      }
      byHeadings[currHeading].push(row);
    });
    return byHeadings;
  };

  _this.markupIncidents = function(incidents) {
    return markupSection({
      rows: incidents,
      filter: function(i) { return i.location; },
      group: function(i) { return i.incidentType; },
      markupBody: function(impacts, type) {
        return '<h3>' + type + '</h3>' + markupRows(impacts, markupIncident);
      },
    });
  };

  _this.markupClosures = function(rows) {
    return markupSection({
      rows: rows,
      filter: function(c) {
        return c.location;
      },
      group: function(closure) {
        return convertTime(closure.closureBegin) +
          (closure.closureEnd ? ' – ' + convertTime(closure.closureEnd) : '');
      },
      markupBody: function(closures, range) {
        return '<h3>Closures scheduled ' + range + '</h3>' + markupRows(closures, markupClosure);
      },
    });
  };

  _this.markupWeekendImpacts = function(rows) {
    return markupSection({
      rows: rows,
      filter: function(i) { return i.event; },
      group: function(i) { return i.day; },
      markupBody: function(impacts, day) {
        return '<h3>' + day + '</h3>' + markupRows(impacts, markupImpact);
      },
    });
  };

  return _this;
};

lexTickerDom = function() {
  var $ = jQuery;
  var baseUrl = 'https://lfucg.github.io/traffic-data';
  var ticker = lexTicker();

  var displayClosures = function(results) {
    $('.lex-traffic-scheduledClosures').html(ticker.markupClosures(results.data));
  };

  var displayIncidents = function(incidents) {
    $('.lex-traffic-incidents').html(ticker.markupIncidents(incidents));
  };

  var displayWeekendImpacts = function(results) {
    var html = ticker.markupWeekendImpacts(results.data);

    $('.lex-traffic-weekendImpacts').html(html);
  };

  var isWeekend = function() {
    // grabbed this mostly wholesale from old website
    var curDate=new Date();
    var curDay=curDate.getDay();
    var weekendCutOver=new Date(curDate.getFullYear(), curDate.getMonth(), curDate.getDate(), 15, 30);
    var weekdayCutOver=new Date(curDate.getFullYear(), curDate.getMonth(), curDate.getDate(), 20, 0);

    if(((curDay===0)&&(curDate<weekdayCutOver))||(curDay===6)||((curDay===5)&&(curDate>=weekendCutOver)))
    {
      return true;
    }
  };

  var displayWeekdayOrWeekend = function() {
    if (isWeekend() || window.location.search.match('show-weekend=true')) {
      Papa.parse(githubUrl("/weekend-impacts.csv"), {
        download: true,
        header: true,
        complete: displayWeekendImpacts,
      });
    } else {
      Papa.parse(githubUrl("/scheduled-closures.csv"), {
        download: true,
        header: true,
        complete: displayClosures,
      });
    }
  };

  var githubUrl = function(file) {
    var epoch = (new Date()).getTime();
    return (baseUrl + file + "?breakcache=" + epoch);
  };

  window.refreshTicker = function() {
    $.get(githubUrl("/traffic-incidents.csv"), function(results, statusCode, req) {
      var updated = moment(new Date(req.getResponseHeader('Last-modified')));
      $('.lex-traffic-lastUpdated').html(updated.format('MM/DD/YYYY hh:mm:ss a'));
      $('.lex-traffic-pageRefreshed').html(moment().format('MM/DD/YYYY hh:mm:ss a'));

      var incidents = Papa.parse(results, { header: true }).data;
      displayIncidents(incidents);
    });
    displayWeekdayOrWeekend();
  };

  window.refreshTicker();
  setInterval(refreshTicker, 60000);
};

/* test env wont have window */
if (window) {
  lexTickerDom();
}
