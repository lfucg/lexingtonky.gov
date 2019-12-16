(function () {
    var $ = jQuery;

    $('.list-switch').click(function () {
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
            'opacity': '100%'});
        $('#calendar').css('display', 'none');
        $('.calendar-key').css('display', 'none');
    });

    $('.month-switch').click(function () {
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
    });
}());