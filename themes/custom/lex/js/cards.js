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
        if ($(window).width() >= 992) {
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
                        $(this).css({
                            'margin-top': margtop+(height-expandheight)/2, 
                            'margin-bottom': margbottom+(height-expandheight)/2, 
                            'margin-right': margright+(width-expandwidth)/2, 
                            'margin-left': margleft+(width-expandwidth)/2, 
                            'width': expandwidth + 'px'
                        });
                    },
                    //Returns everything to normal on mouseout.
                    function() {
                        $(this).css({
                            'margin-top': '', 
                            'margin-bottom': '', 
                            'margin-right': '', 
                            'margin-left': '', 
                            'width': ''
                        });
                        height = '';
                        width = '';
                        expandwidth = '';
                        expandheight = '';
                        margtop = '';
                        margbottom = '';
                        margright = '';
                        margleft = '';
                    }
                );
            });
        }else {
            //This makes sure that if the window is small enough to be a mobile screen that dynamic expansion
            // doesn't occur, and the variables are all reset to facilitate window resizing.
            $('expandable').each(function() {
                $(this).css({
                    'margin-top': '',
                    'margin-bottom': '',
                    'margin-right': '',
                    'margin-left': '',
                    'width': ''
                });
                height = '';
                width = '';
                expandwidth = '';
                expandheight = '';
                margtop = '';
                margbottom = '';
                margright = '';
                margleft = '';
            });
        }
    };


    $(window).resize(dynamicExpansion);


}());