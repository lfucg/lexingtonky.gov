(function () {
    var $ = jQuery;

    var i = 1;

    $('.event-card').each(function () {
        if (i%2 === 0) {
            $(this).addClass('event-right');
        }
        i++;
    });

    function dynamicExpansion () {
    // Gets each element with expandable class
        $('.expandable').each(function () {

            //Establishes initial variables for height and width, as well as calculating expanded width based on a 10 percent
            //increase. The expanded height is initialized for later calculation as it is set to auto and unknown at this time
            //due to the element not adhering to a static aspect ratio.
            var height = parseInt($(this).css('height'));
            var width = parseInt($(this).css('width'));
            var expandwidth = width * 1.1;
            var expandheight;
            var margtop = parseInt($(this).css('margin-top'));
            var margbottom = parseInt($(this).css('margin-bottom'));
            var margright = parseInt($(this).css('margin-right'));
            var margleft = parseInt($(this).css('margin-left'));


            //For each iteration, on hover expanded height is calculated quickly, and a negative margin is applied to subtract
            //an amount equal to the expansion, so that the element does not take up more or less space on the page.
            $(this).hover(
                function() {
                    expandheight = parseInt($(this).css('height'));
                    console.log(height, expandheight, width, expandwidth);
                    $(this).css({'margin-top': margtop+(height-expandheight)/2, 'margin-bottom': margbottom+(height-expandheight)/2, 'margin-right': margright+(width-expandwidth)/2, 'margin-left': margleft+(width-expandwidth)/2, 'width': expandwidth + 'px'});
                },
                //Returns everything to normal on mouseout.
                function() {
                    $(this).css({'margin-top': margtop, 'margin-bottom': margbottom, 'margin-right': margright, 'margin-left': margleft, 'width': width});
                }
            );

        });
    };


    window.resize(dynamicExpansion);
}());