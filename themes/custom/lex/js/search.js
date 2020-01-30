(function () {
    var $ = jQuery;

    var pathName = window.location.search;

    var searchTerm = pathName.substr(pathName.indexOf('=') + 1);

    $('.results-header').each(function () {
        $(this).text('Results for: ' + searchTerm);
    });

    $('.views-field-type').each(function () {
        if ($(this).find('.field-content').text() == 'Service guide' || $(this).find('.field-content').text() == 'Organization page' || $(this).find('.field-content').text() == 'Full-bleed landing page') {
            $(this).find('.field-content').text() == 'Page';
        } else if ($(this).find('.field-content').text() == 'Meeting/notice') {
            $(this).find('.field-content').text() == 'Meeting';
        }
    });


}());