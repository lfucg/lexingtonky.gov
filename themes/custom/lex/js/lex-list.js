(function() {
  "use strict";

  var $ = jQuery;

  var options = {
    valueNames: [ 'js-lex-filter-item' ]
  };

  var userList = new List(document.getElementsByClassName('js-lex-filter-block')[0], options);
  $('.js-lex-filter-block .lex-hide-initially').removeClass('lex-hide-initially');
}());
