(function() {
  var $ = jQuery;
  Papa.parse("/scheduled-closures.csv", {
    download: true,
    header: true,
    complete: function(results, file) {
      var html = '';
      var convertTime = function(military) {
        return military > 12 ? (military - 12 + ' p.m.') : (military + ' a.m.');
      }
      var timeRanges = _.groupBy(results.data, function(range) {
        return convertTime(range['closure-begin']) + ' – ' + convertTime(range['closure-end']);
      });
      var markupClosure = function(closure) {
        return '<li>' + closure.location + ' – ' + closure.impact + ' Thru ' + closure['closed-thru'] + '</li>';
      }
      var markupRange = function(closures, range) {
        return '<h3>Closures scheduled from ' + range + '</h3>' +
        '<ul>' +
          _.map(closures, function(c) { return markupClosure(c) }).join('') +
        '</ul>';
      }
      html += _.map(timeRanges, markupRange).join('');
      $('.lex-region-content article').html(html);
    }
  });
}());
