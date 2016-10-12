var assert = require('assert');
window = undefined;
_ = require('underscore'); // cheat and let ticker use node's underscore

describe('Transform incidents', function() {
  it('should group by sectionHeadings', function() {
    var rows = [
      {sectionHeading: 'First heading'},
      {sectionHeading: ''},
      {sectionHeading: 'Second heading'},
      {sectionHeading: ''},
    ];
    var expected = {
      'First heading':  [rows[0], rows[1]],
      'Second heading': [rows[2], rows[3]]
    };
    assert.deepEqual(lexTicker().groupByHeading(rows), expected);
  });

  it('should markup weekend impacts', function() {
    var rows = [
      {sectionHeading: 'Heading'},
      {sectionHeading: '', day: "Saturday October 8", event: "An event", timeBegin: '8 am'},
    ];
    var expected = '<h2>Heading</h2><h3>Saturday October 8</h3><ul><li>An event 8 am</li></ul>';
    assert.equal(lexTicker().markupWeekendImpacts(rows), expected);
  });
});
