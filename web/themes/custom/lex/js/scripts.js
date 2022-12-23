;
(function($) {
  // $('.lex-hide-unless-javascript').removeClass('lex-hide-unless-javascript');

  $.LexingtonFilterBlock = function(el) {
    var options = {
      valueNames: [ 'js-lex-filter-item' ]
    };

    var userList = new List(el, options);
  }

  /* duplicated in browse-columns.js, lex-gis.js */
  $.LexingtonFilterBlock(document.getElementsByClassName('js-lex-filter-block')[0]);

  // Now correct titles and search terms on the search pages.
  if ($('body.path-search')) {
    var search = getParameterByName('search_api_fulltext');
    if (search) {
      $('input[type="search"]').val(search);
      $('h1').append(' for "' + search + '"');
    }
  }

}(jQuery));

function getParameterByName(name, url) {
    if (!url) url = window.location.href;
    name = name.replace(/[\[\]]/g, "\\$&");
    var regex = new RegExp("[?&]" + name + "(=([^&#]*)|&|#|$)"),
        results = regex.exec(url);
    if (!results) return null;
    if (!results[2]) return '';
    return decodeURIComponent(results[2].replace(/\+/g, " "));
}
