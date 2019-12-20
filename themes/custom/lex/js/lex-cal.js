(function () {
    var $ = jQuery;

    $('#sidebar-calendar').css('display', 'inline-block');

    var combinedHeight= '';
    var combinedTopMarg= '';
    var height= '';
    var paddingTop = '';
    var paddingBottom= '';
    var feedbackHeight = '';
    var feedbackPaddingTop = '';
    var feedbackPaddingBottom = '';
    var feedbackTotal = '';
    var toolbarHeight = '';
    var adminHeight = '';

    var listMode = '';
    var monthMode = '';
    var dualMode =  '';

    var num = '';

    function heightCalc() {
        height = parseInt($('main').height());
        paddingTop = parseInt($('main').css('padding-top'));
        paddingBottom = parseInt($('main').css('padding-bottom'));
        feedbackHeight = parseInt($('.lex-region-feedback').height());
        feedbackPaddingTop = parseInt($('.lex-region-feedback').css('padding-top'));
        feedbackPaddingBottom = parseInt($('.lex-region-feedback').css('padding-bottom'))
        feedbackTotal = feedbackHeight + feedbackPaddingTop + feedbackPaddingBottom;

        if (parseInt($('#toolbar-bar').height()) > 0) {
            toolbarHeight = parseInt($('#toolbar-bar').height());
        } else {
            toolbarHeight = 0;
        }

        if (parseInt($('#toolbar-item-administration-tray').height()) > 0) {
            adminHeight = parseInt($('#toolbar-item-administration-tray').height());
        } else {
            adminHeight = 0;
        }

        combinedHeight = height + feedbackTotal + paddingTop + paddingBottom;
        combinedTopMarg = parseInt($('.lex-region-breadcrumb').height() + $('.sticky-top').height() + $('#block-lex-headerquicklinks').height() + toolbarHeight + adminHeight);

        $('#sidebar-calendar').css({
            'height': combinedHeight + 'px',
            'margin-top': combinedTopMarg + 'px'
        });
        $('.fc-scroller').css('max-height', combinedHeight + 'px');
    }

    $(window).on('load', function () {
        //get the natural page height -set it in variable above.

        if ($(window).width() >= 768) {
            dualMode = true;
            heightCalc();
        }else {
            monthMode = true;
            $('#sidebar-calendar').css('visibility', 'hidden');
        }
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
            $('#sidebar-calendar').css('visibility', 'hidden');
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
                'display': 'inline-block',
                'visibility': 'visible'
            });
            $('.sidecal-col').css({
                'height': 'auto',
            });
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
            heightCalc();
        }else {
            monthMode = true;
            dualMode = false;
            listMode = false;
            modeCheck();
            heightCalc();
        }
    });

    $(document).on('click', '.month-dot', function () {
        if (dualMode==true) {
            num = $(this).attr('id').substr(6);

            $('.list-event-container').each(function () {
                if ($(this).attr('id').substr(5) == num) {
                    $(this)[0].scrollIntoView();
                }
            });
        }else {
            num = $(this).attr('id').substr(6);
            dualMode = false;
            monthMode= false;
            listMode = true;
            modeCheck();

            $('.list-event-container').each(function () {
                if ($(this).attr('id').substr(5) == num) {
                    $(this)[0].scrollIntoView();
                }
            });
        }
    });

    $(document).on('click', '.main-prev', function () {
        $('.side-prev').trigger('click');
    });
    $(document).on('click', '.main-next', function () {
        $('.side-next').trigger('click');
    });
}());