(function($) {

  let windowWidth = $( window ).width();
  $( window ).resize( function() {
    windowWidth = $( window ).width();
  });

  $('.fade, #nav-icon').click( function() {
    $("#nav-icon").toggleClass('open');
    $("#takeoverNav").toggleClass('show');
    $(".fade").toggleClass('show');
    $("body").toggleClass('no-scroll');
    $('.menu-label').html($('.menu-label').html() == 'Menu' ? 'Close' : 'Menu');
  });
}(jQuery));
