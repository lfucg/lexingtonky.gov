(function () {
    var $ = jQuery;

    var pathName = window.location.search;

    var searchTerm = pathName.substr(pathName.indexOf('=') + 1);

    $('.results-header').each(function () {
        $(this).text('Results for: ' + searchTerm);
    });

    $('.views-field-type').each(function () {
        var text = $(this).find('.field-content').text();

        text.replace('Service guide', 'Page');
        text.replace('Organization page', 'Page');
        text.replace('Full-bleed landing page', 'Page');
        text.replace('Meeting/notice', 'Meeting');

        $(this).find('.field-content').text() == text;
    });


}());