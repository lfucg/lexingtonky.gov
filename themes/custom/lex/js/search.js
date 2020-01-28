(function () {
    var $ = jQuery;

    var pathName = window.location.search;

    var searchTerm = pathName.substr(pathName.indexOf('=') + 1);

    $('.results-header').each(function () {
        $(this).text('Results for: ' + searchTerm);
    });


}());