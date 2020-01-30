(function () {
    var $ = jQuery;

    var pathName = window.location.search;

    var searchTerm = pathName.substr(pathName.indexOf('=') + 1);

    $('.results-header').each(function () {
        $(this).text('Results for: ' + searchTerm);
    });

    $('.views-field-type').each(function () {
        console.log($(this).find('.field-content').text());
        
        $(this).find('.field-content').text().replace('Service guide', 'Page');
        $(this).find('.field-content').text().replace('Organization page', 'Page');
        $(this).find('.field-content').text().replace('Full-bleed landing page', 'Page');
        $(this).find('.field-content').text().replace('Meeting/notice', 'Meeting');
    });


}());