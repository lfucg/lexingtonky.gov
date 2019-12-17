(function () {
    var $ = jQuery;

    var combinedHeight= '';
    var combinedTopMarg= '';
    var height= '';
    var paddingTop = '';
    var paddingBottom= '';
    var feedbackHeight = '';
    var feedbackPaddingTop = '';
    var feedbackPaddingBottom = '';
    var feedbackTotal = '';

    var listMode = '';
    var monthMode = '';
    var dualMode =  '';

    var num = '';

    
    $(window).on('load', function () {
        //get the natural page height -set it in variable above.

        height = parseInt($('main').height());
        paddingTop = parseInt($('main').css('padding-top'));
        paddingBottom = parseInt($('main').css('padding-bottom'));
        feedbackHeight = parseInt($('.lex-region-feedback').height());
        feedbackPaddingTop = parseInt($('.lex-region-feedback').css('padding-top'));
        feedbackPaddingBottom = parseInt($('.lex-region-feedback').css('padding-bottom'))
        feedbackTotal = feedbackHeight + feedbackPaddingTop + feedbackPaddingBottom;
        combinedHeight = height + feedbackTotal + paddingTop + paddingBottom;
        combinedTopMarg = parseInt($('.lex-region-breadcrumb').height() + $('.sticky-top').height() + $('#block-lex-headerquicklinks').height());

        $('#sidebar-calendar').css({
            'height': combinedHeight + 'px',
            'margin-top': combinedTopMarg + 'px'
        });
        $('.fc-scroller').css('max-height', combinedHeight + 'px');
    });

    $('.list-switch').click(function () {
        listMode = true;
        monthMode = false;
        dualMode = false;
        
        modeCheck();
    });

    $('.month-switch').click(function () {
        monthMode = true;
        listMode = false;
        dualMode = false;

        modeCheck();
    });

    function modeCheck() {
        if (monthMode === true) {
            $('.month-switch').css({
                'background-color': 'white',
                'color': '#004585'
            });
            $('.list-switch').css({
                'background-color': '#EFEFEF',
                'color': '#353535'
            });
            $('#sidebar-calendar').css('display', 'none');
            $('#calendar').css('display', 'block');
            $('.calendar-key').css('display', 'block');
        }else if (listMode == true) {
            $('.list-switch').css({
                'background-color': 'white',
                'color': '#004585'
            });
            $('.month-switch').css({
                'background-color': '#EFEFEF',
                'color': '#353535'
            });
            $('#sidebar-calendar').css({
                'display': 'block',
                'opacity': '100%'
            });
            $('.sidecal-col').css('height', 'auto');
            $('#calendar').css('display', 'none');
            $('.calendar-key').css('display', 'none');
        }else if (dualMode == true) {
            $('#calendar').css('display', 'block');
            $('#sidebar-calendar').css('display', 'block');
        }
    }


    $(window).resize(function () {
        if ($(window).width() >= 768) {
            dualMode = true;
            monthMode = false;
            listMode = false;
            modeCheck();
        }else {
            monthMode = true;
            dualMode = false;
            listMode = false;
            modeCheck();
        }
    });

    $(document).on('click', '.month-dot', function () {
        num = $(this).attr('id').substr(6);

        $('.list-event-container').each(function () {
            if ($(this).attr('id').substr(5) == num) {
                $(this)[0].scrollIntoView();
            }
        });
    });
}());