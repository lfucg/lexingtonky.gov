(function () {
    var $ = jQuery;

    var pathName = window.location.search;

    var searchTerm = pathName.substr(pathName.indexOf('=') + 1);

    $('.results-header').each(function () {
        $(this).text('Results for: ' + searchTerm);
    });

    $('.views-element-container').each(function () {
        var text = $(this).find('.js-view-dom-id-32207e6735f7d828021fb806b2fb97bf43fd7e816ae87f5a7603c00f314a4b8b').text();

        console.log(text);
    });

    $('.views-field-type').each(function () {
        var text = $(this).find('.field-content').text();

        if (text == 'Service guide' || text == 'Organization page' || text == 'Full-bleed landing page') {
            text = text.replace('Service guide', 'Page');
        }else if (text == 'Meeting/notice') {
            text = text.replace('Meeting/notice', 'Meeting');
        }else {
            return text;
        }
        
        $(this).find('.field-content').text().replace($(this).find('.field-content').text(), text);
    });


}());