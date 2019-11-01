(function($) {

  let windowWidth = $( window ).width();
  $( window ).resize( function() {
    windowWidth = $( window ).width();
  });

  $('.navbar-button,.menu-label,.fade').click( function() {
    $("#nav-icon").toggleClass('open');
    $("#takeoverNav").toggleClass('show');
    $(".fade").toggleClass('show');
    $("body").toggleClass('no-scroll');
    $('.menu-label').html($('.menu-label').html() == 'Menu' ? 'Close' : 'Menu');


    // going to need to use this for nav set up.
    if (windowWidth > 998) {
      console.log('bigSCREENS');
    } else {
      console.log('littlescreens');
    }
  });
}(jQuery));
