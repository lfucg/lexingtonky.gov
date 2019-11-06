(function () {
    var $ = jQuery;

    $('.expandable').each(function () {

        var height = parseInt($(this).css('height'));
        var width = parseInt($(this).css('width'));
        var expandwidth = width * 1.1 + 'px';
        var expandheight;

        $(this).hover(
            function() {
                expandheight = parseInt($(this).css('height'));
                $(this).css({'margin-top': (height-expandheight)/2, 'margin-bottom': (height-expandheight)/2, 'width': expandwidth});
            },
            function() {
                $(this).css({'margin-top': 'auto', 'margin-bottom': 'auto', 'width': width});
            }
        );

    });
}());